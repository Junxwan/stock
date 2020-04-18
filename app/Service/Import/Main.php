<?php

/**
 * 匯入主力買賣超分點
 */

namespace App\Service\Import;

use App\Exceptions\StockException;
use Illuminate\Support\Collection;

class Main extends Import
{
    /**
     * 買超分點
     */
    const BUY = 'buy';

    /**
     * 賣超分點
     */
    const SELL = 'sell';

    /**
     * @var array
     */
    private $start = [
        self::BUY => 2,
        self::SELL => 17,
    ];

    /**
     * @param Collection $data
     *
     * @return bool
     * @throws StockException
     */
    protected function insert(Collection $data): bool
    {
        $insert = [];

        $codes = $this->repo->codes($this->date);

        /**
         * 寫入的股票總數
         * 寫入的分點總數
         * 總分點總數
         */
        $saveCodeTotal = 0;
        $saveMainTotal = 0;
        $mainTotal = 0;
        $code = 0;
        $insert = [];

        try {
            foreach ($data->all() as $i => $value) {
                $code = $value[0];
                if (isset($codes[$value[0]])) {
                    continue;
                }

                $mainTotal += count(array_filter(array_slice($value, 2, 30)));

                $models = array_merge(
                    $this->newModels($value, self::BUY),
                    $this->newModels($value, self::SELL)
                );

                $insert = array_merge($insert, $models);

                $saveCodeTotal++;

                if ($i % 50 == 0) {
                    $this->info($i);
                }

                if (count($insert) <= 5000) {
                    continue;
                }

                if ($this->repo->batchInsert($insert)) {
                    $saveMainTotal += count($insert);
                    $insert = [];
                } else {
                    break;
                }
            }
        } catch (\Exception $e) {
            throw new StockException($e, $code);
        }

        if ($this->repo->batchInsert($insert)) {
            $saveMainTotal += count($insert);
        }

        $this->info('date: ' . $this->date);
        $this->info('code total: ' . $data->count() . ' save code total: ' . $saveCodeTotal);
        $this->info('main total: ' . $mainTotal . ' save main total: ' . $saveMainTotal);

        return true;
    }

    /**
     * 建立model資料
     *
     * @param array $stock
     *
     * @return array
     */
    private function newModels(array $stock, string $action): array
    {
        $models = [];
        $start = $this->start[$action];

        for ($i = $start; $i <= $start + 14; $i++) {
            if ($stock[$i] == '') {
                continue;
            }

            $model = [
                'code' => $stock[0],
                'date' => $this->date,
                'name' => $stock[$i],
                'count' => $stock[$i + 30],
            ];

            if ($action == self::SELL) {
                $model['count'] = -$model['count'];
            }

            $models[] = $model;
        }

        return $models;
    }
}
