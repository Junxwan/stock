<?php

/**
 * 分析個股單日行情
 */

namespace App\Service\Result;

use App\Exceptions\StockException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Price extends Result
{
    /**
     * @param string $date
     *
     * @return mixed|void
     */
    public function save(string $date, array $parameter = [])
    {
        try {
            $dateTime = Carbon::createFromFormat('Y-m-d', $date);

            if (! $dateTime->isWeekday()) {
                throw new \Exception($date . ' is not week');
            }

            $data = $this->priceRepo->date($date);

            if ($data->isEmpty()) {
                throw new \Exception($date . ' not price');
            }

            if (! $this->priceInsert($data, $dateTime)) {
                return false;
            }

            return $this->mainKeyInsert($date, $parameter['key_path']);
        } catch (\Exception $e) {
            Log::error(($e->getCode() != 0 ? 'code: ' . $e->getCode() : '') . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

    /**
     * 分析個股
     *
     * @param Collection $data
     * @param \Carbon\Carbon $dateTime
     *
     * @return bool
     * @throws \Exception
     */
    private function priceInsert(Collection $data, \Carbon\Carbon $dateTime)
    {
        $date = $dateTime->toDateString();
        $yesterday = $this->yesterday($date);
        $result = $this->result($date);
        $epss = $this->eps($dateTime->subYear()->year);
        $insert = [];
        $notEpsCodes = [];
        $notYesterdayCodes = [];

        try {
            foreach ($data as $value) {
                if (isset($result[$value->code])) {
                    continue;
                }

                if (! isset($yesterday[$value->code])) {
                    $notYesterdayCodes[] = $value->code;
                    continue;
                }

                $yesterdayPrice = $yesterday[$value->code];

                $code = $value->code;

                $rank = $this->rank(
                    $value->bb_top,
                    $value->bb_below,
                    $value->month_ma,
                    $value->close
                );

                $pe = 0;
                $yield = 0;
                $cashYield = 0;
                $volume20Multiple = 0;

                if (isset($epss[$value->code])) {
                    $eps = $epss[$value->code];

                    if ($eps['eps'] > 0) {
                        $pe = round($value->close / $eps['eps']);
                    }

                    if ($eps['dividend_total'] > 0) {
                        $yield = round(($eps['dividend_total'] / $value->close) * 100, 1);
                    }

                    if ($eps['cash_yield'] > 0) {
                        $cashYield = round(($eps['dividend_cash_total'] / $value->close) * 100, 1);
                    }
                } else {
                    $notEpsCodes[] = $code;
                }

                if ($value->volume != 0 && $value->volume_20 != 0) {
                    $volume20Multiple = round($value->volume / $value->volume_20, 1);
                }

                $insert[] = [
                    'code' => $value->code,
                    'date' => $value->date,
                    'rank' => $rank->value,
                    'bandwidth' => $this->bandwidth($value->bb_top, $value->bb_below),
                    'pe' => $pe,
                    'yield' => $yield,
                    'cash_yield' => $cashYield,
                    'month_Slope' => $this->countSlopeNum($value->month_ma, $yesterdayPrice->month_ma),
                    'bb_top_Slope' => $this->countSlopeNum($value->bb_top, $yesterdayPrice->bb_top),
                    'bb_below_Slope' => $this->countSlopeNum($value->bb_below, $yesterdayPrice->bb_below),
                    'top_diff' => $rank->topDiffLie,
                    'below_diff' => $rank->belowDiffLie < 0 ? $rank->belowDiffLie * -1 : $rank->belowDiffLie,
                    'high_stray' => round(($value->close / $value->last_year_max) * 100),
                    'low_stray' => round((($value->close / $value->last_year_min) - 1) * 100),
                    'volume_20_multiple' => $volume20Multiple,
                ];
            }
        } catch (\Exception $e) {
            throw new StockException($e, $code);
        }

        $result = true;
        if (count($insert) > 0) {
            $result = $this->priceResultRepo->batchInsert($insert);
        }

        $this->info('total: ' . count($data) . ' save: ' . count($insert));

        if (count($notEpsCodes) > 0) {
            $this->info('=================================================');
            $this->info('not eps code: ' . implode(',', $notEpsCodes) . ' total: ' . count($notEpsCodes));
        }

        if (count($notYesterdayCodes) > 0) {
            $this->info('not yesterday code: ' . implode(',',
                    $notYesterdayCodes) . ' total: ' . count($notYesterdayCodes));
        }

        return $result;
    }

    /**
     * 個股關鍵分點進出
     *
     * @param string $date
     * @param string $path
     *
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function mainKeyInsert(string $date, string $path)
    {
        $keyResult = $this->keyResult($date);
        $key = $this->readKey($path);
        $main = $this->main($date);
        $insert = [];

        foreach ($key as $c => $value) {
            if (isset($keyResult[$c])) {
                continue;
            }

            $b = $main[$c]['buy']->whereIn('name', $value['key'])->all();
            $s = $main[$c]['sell']->whereIn('name', $value['key'])->all();

            foreach ($b as $order => $item) {
                $insert[] = [
                    'code' => $c,
                    'date' => $date,
                    'point_code' => $item['code'],
                    'type' => true,
                    'order' => $order + 1,
                ];
            }

            foreach ($s as $order => $item) {
                $insert[] = [
                    'code' => $c,
                    'date' => $date,
                    'point_code' => $item['code'],
                    'type' => false,
                    'order' => $order + 1,
                ];
            }
        }

        $result = true;
        if (count($insert) > 0) {
            $result = $this->keyResultRepo->batchInsert($insert);
        }

        $this->info('=================================================');
        $this->info('main key total: ' . count($key) . ' save: ' . count($insert));

        return $result;
    }

    /**
     * 關鍵分點
     *
     * @return Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function readKey(string $path)
    {
        $spreadsheet = IOFactory::load($path);
        $data = $spreadsheet->getSheet(3)->toArray();
        unset($data[0]);

        foreach ($data as $item) {
            $key = collect($item)->slice(2, 5)->whereNotNull();
            if ($key->isNotEmpty()) {
                $res[$item[0]] = [
                    "code" => $item[0],
                    "name" => $item[1],
                    "key" => $key->toArray(),
                ];
            }
        }

        return collect(isset($res) ? $res : []);
    }

    /**
     * 位階
     *
     * @param mixed $top
     * @param mixed $below
     * @param mixed $month
     * @param mixed $price
     *
     * @return object
     */
    private function rank($top, $below, $month, $price): object
    {
        $rank = new \stdClass();

        $rank->value = 0;
        $rank->direction = 0;
        $rank->topDiffLie = 0;
        $rank->belowDiffLie = 0;

        if ($month < $price) {
            $rank->direction = 1;
            $diff = $top - $price;
            $width = $top - $month;
        } else {
            $rank->direction = -1;
            $diff = $price - $below;
            $width = $month - $below;
        }

        if ($diff > 0) {
            $rank->value = floor(($width - $diff) / ($width / 10)) * $rank->direction;
            $rank->topDiffLie = round((($top / $price) - 1) * 100, 1);
            $rank->belowDiffLie = round((($below / $price) - 1) * 100, 1);
        }

        return $rank;
    }

    /**
     * 帶寬
     *
     * @param $top
     * @param $below
     *
     * @return int
     */
    private function bandwidth($top, $below): int
    {
        if ($top == 0 && $below == 0) {
            return 0;
        }

        return round((($top / $below) - 1) * 100);
    }

    /**
     * 計算斜率
     *
     * @param mixed $value1
     * @param mixed $value2
     *
     * @return float
     */
    private function countSlopeNum($value1, $value2): float
    {
        if ($value1 == 0 || $value2 == 0) {
            return 0;
        }

        $slope = (($value1 / $value2) - 1) * 100;

        $result = round($slope, 1);

        if ($result == 0) {
            $result = $slope > 0 ? 0.1 : -0.1;
        }

        return $result;
    }
}
