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
    protected function insert(Collection $data): bool
    {
        $dates = $this->openDate->whereBetween('t', [$this->year . '0101', $this->year . '1231']);
        $date = $this->getDate($data);
        $this->checkYearDate($date, $dates);

        $this->info('=======================================================');

        $data = $this->toDateKeyPrice($data, $date);
        $codes = $this->stockRepo->all()->pluck('code')->toArray();
        $skipCode = $this->xlsx->getParam('skip');
        $isSKip = $skipCode == '' ? false : true;

        foreach ($codes as $i => $code) {
            if ($isSKip) {
                if ($skipCode == $code) {
                    $isSKip = false;
                }
                continue;
            }

            [$inserts, $existDateTotal, $notOpenTotal] = $this->newInserts($code, $dates, $data);

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
        $keys = $this->priceKeys();
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
     *
     * @return array
     */
    private function newInserts(string $code, Collection $dates, Collection $data): array
    {
        $existDateTotal = 0;
        $notOpenTotal = 0;
        $inserts = [];
        $prices = $this->prices($code, $this->year);
        $keys = $this->priceKeys();
        $open = $data->get('open')->get($code);

        foreach ($dates as $d) {
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
                $value = $data->get($k)->get($code);
                $insert->put($k, $this->format($value[$dt]));
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
            'last_year_max',
            'last_year_min',
            'last_year_date',
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
}
