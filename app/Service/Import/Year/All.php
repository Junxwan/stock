<?php

namespace App\Service\Import\Year;

use App\Exceptions\StockException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class All extends Import
{
    /**
     * @param Collection $data
     *
     * @return bool
     * @throws StockException
     */
    protected function insert(Collection $all): bool
    {
        $data = $all->get($this->year);
        $date = $this->getDate($data);
        $dates = $this->openDate->whereBetween('date', [$this->year . '-01-01', $date->get('open')[0]]);
        $this->checkYearDate($date, $dates);

        $lastYear = $this->year - 1;
        $lastYearDates = $this->openDate->whereBetween('t', [$lastYear . '0101', $lastYear . '1231']);
        $lastYearData = $all->get($lastYear);
        $lastYearDate = $this->getDate($lastYearData);
        $this->checkYearDate($lastYearDate, $lastYearDates);

        $this->info('=======================================================');

        $data = $this->toDateKeyPrice($data, $date);
        $lastYearData = $this->toDateKeyPrice($lastYearData, $lastYearDate);
        $codes = $this->stockRepo->all()->pluck('code')->toArray();
        $skipCode = $this->xlsx->getParam('skip');
        $isSKip = $skipCode == '' ? false : true;

        $allYear = $this->getAllYear($data, $lastYearData);

        $this->info('=======================================================');

        foreach ($codes as $i => $code) {
            if ($isSKip) {
                if ($skipCode == $code) {
                    $isSKip = false;
                }
                continue;
            }

            [$inserts, $existDateTotal, $notOpenTotal] = $this->newInserts($code, $dates, $data, $allYear);

            // 撿查db資料筆數與實際資料是否有出入
            $num = count($data->get('open')->get($code));
            if ($num != count($inserts) + $existDateTotal + $notOpenTotal) {
                $this->exception('insert count is not ' . $num, $code);
            }

            if (count($inserts) > 0 && ! $this->priceRepo->batchInsert($inserts)) {
                $this->exception('insert error', $code);
            }

            $this->checkPrice($code, $dates, $data);

            $total = $dates->count();
            $insertTotal = count($inserts);

            $s = $this->year .
                ' code:' . $code .
                ' total:' . $total .
                ' insert:' . $insertTotal .
                ' exist:' . $existDateTotal .
                ' not open:' . $notOpenTotal .
                ' diff:' . ($total - ($insertTotal + $existDateTotal + $notOpenTotal) .
                    ' t:' . ($insertTotal + $existDateTotal + $notOpenTotal) .
                    ' i:' . $i
                );
            $this->info($s);
            Log::info($s);
        }

        return true;
    }

    /**
     * 取出個股當年度所有資料並撿查所欄位資料是實際資料是否有落差
     *
     * @param string $code
     * @param Collection $dates
     * @param Collection $data
     *
     * @throws StockException
     */
    private function checkPrice(string $code, Collection $dates, Collection $data)
    {
        $keys = $this->getCheckPriceKey();
        $prices = $this->prices($code, $this->year);
        foreach ($dates as $d) {
            $dt = $d['date'];

            if (! isset($prices[$dt])) {
                continue;
            }

            foreach ($keys as $k) {
                $v = $data->get($k)->get($code)[$dt];

                if ($prices[$dt][$k] != $this->format($v)) {
                    $this->exception($dt . ' ' . $k . ' is not [' . $v . ']', $code);
                }
            }
        }
    }

    /**
     * 建立個股年度資料
     *
     * @param string $code
     * @param Collection $dates
     * @param Collection $data
     * @param Collection $allYear
     *
     * @return array
     */
    private function newInserts(string $code, Collection $dates, Collection $data, Collection $allYear): array
    {
        $existDateTotal = 0;
        $notOpenTotal = 0;
        $inserts = [];
        $prices = $this->prices($code, $this->year);
        $keys = $this->priceKeys();
        $open = $data->get('open')->get($code);

        $index = -1;

        $stray = [
            '5stray' => $data->get('5ma')->get($code),
            '10stray' => $data->get('10ma')->get($code),
        ];

        $max = $allYear->get('max')[$code];
        $min = $allYear->get('min')[$code];
        $volume = $allYear->get('volume')[$code];
        $close = $data->get('close')->get($code);

        foreach ($dates as $i => $d) {
            $index++;

            $dt = $d['date'];

            // 撿查該日期的個股資料是否已存在
            if (isset($prices[$dt])) {
                $existDateTotal++;
                continue;
            }

            if ($open[$dt] == 0) {
                $notOpenTotal++;
                continue;
            }

            $insert = collect([
                'code' => $code,
                'date' => $dt,
            ]);

            // put個股資料欄位
            foreach ($keys as $k) {
                switch ($k) {
                    case 'last_year_max':
                        $value = $max->slice($index, 250)
                            ->filter()
                            ->sort()
                            ->last();
                        break;
                    case 'last_year_min':
                        $value = $min->slice($index, 250)
                            ->filter()
                            ->sort()
                            ->first();
                        break;
                    case '5stray':
                    case '10stray':
                        $value = $this->round(
                            (($close[$dt] / $stray[$k][$dt]) - 1) * 100,
                            2
                        );
                        break;
                    case 'volume20':
                        $value = $this->round(
                            $volume->filter()->slice($index, 20)->sum() / 20
                        );
                        break;
                    default:
                        $value = $this->format($data->get($k)->get($code)[$dt]);
                }

                $insert->put($k, $value);
            }

            $inserts[] = $this->new($insert);
        }

        return [$inserts, $existDateTotal, $notOpenTotal];
    }

    /**
     * 個股欄位資料依照日期分類
     *
     * @param Collection $data
     * @param Collection $date
     *
     * @return Collection
     */
    private function toDateKeyPrice(Collection $data, Collection $date)
    {
        foreach ($data as $key => $values) {
            $this->info('init ' . $key . ' ...');
            $p = [];
            $d = $date->get($key);
            foreach ($values->slice(1) as $value) {
                $code = $value[0];
                foreach (array_slice($value, 2) as $i => $v) {
                    $p[$code][$d[$i]] = $v;
                }
            }

            $data[$key] = collect($p);
        }

        return $data;
    }

    /**
     * @return array
     */
    private function priceKeys()
    {
        return collect(array_keys($this->new(collect([]))))->diff([
            'code',
            'date',
        ])->all();
    }

    /**
     * @return array
     */
    private function getCheckPriceKey()
    {
        return collect($this->priceKeys())->diff([
            'last_year_max',
            'last_year_min',
            '5stray',
            '10stray',
            'volume20',
        ])->all();
    }

    /**
     * @param string $code
     * @param string $message
     *
     * @throws StockException
     */
    private function exception(string $code, string $message)
    {
        throw new StockException(new \Exception($message), $code);
    }

    /**
     * @param Collection $data
     * @param Collection $lastYearData
     *
     * @return Collection
     */
    private function getAllYear(Collection $data, Collection $lastYearData)
    {
        $max = $data->get('max');
        $min = $data->get('min');
        $volume = $data->get('volume');
        $lastYearMax = $lastYearData->get('max');
        $lastYearMin = $lastYearData->get('min');
        $lastVolume = $lastYearData->get('volume');

        foreach ($max as $c => $v) {
            if (isset($lastYearMax[$c])) {
                $v = array_merge($v, $lastYearMax[$c]);
            }

            $max[$c] = collect($v);
        }

        foreach ($min as $c => $v) {
            if (isset($lastYearMin[$c])) {
                $v = array_merge($v, $lastYearMin[$c]);
            }

            $min[$c] = collect($v);
        }

        foreach ($volume as $c => $v) {
            if (isset($lastVolume[$c])) {
                $v = array_merge($v, $lastVolume[$c]);
            }

            $volume[$c] = collect($v);
        }

        return collect([
            'max' => $max,
            'min' => $min,
            'volume' => $volume,
        ]);
    }
}
