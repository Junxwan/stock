<?php

/**
 * 匯入主力買賣超分點
 */

namespace App\Service\Import;

use App\Exceptions\StockException;
use App\Repository\MainRepository;
use App\Service\Xlsx\Xlsx;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
     * @var MainRepository
     */
    private $repo;

    /**
     * Main constructor.
     *
     * @param MainRepository $repo
     * @param Xlsx $xlsx
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(MainRepository $repo, Xlsx $xlsx)
    {
        $this->repo = $repo;
        parent::__construct($xlsx);
    }

    /**
     * 寫入主力買賣超分點
     *
     * 0 代碼
     * 1 名稱
     * 2 ~ 16 買超分點
     * 17 ~ 31 賣超分點
     * 32 ~ 46 買超張數
     * 47 ~ 61 賣超張數
     * 62 成交量
     *
     * @param Collection $data
     *
     * @return bool
     * @throws StockException
     */
    protected function insert(Collection $data): bool
    {
        $codes = $this->getCodes($data);

        if ($this->checkRepeat($codes)) {
            return false;
        }

        if ($this->checkDiff($codes['code'])) {
            return false;
        }

        $existCodes = $this->existCodes();
        $saveMainTotal = 0;
        $mainTotal = 0;
        $code = 0;
        $insert = [];

        try {
            foreach ($data->all() as $i => $value) {
                $code = $value[0];

                // 該股票分點資料已存在不用處理
                if (in_array($code, $existCodes)) {
                    continue;
                }

                if ($value[1] == '' || strlen($value[0]) != 4) {
                    continue;
                }

                $mainTotal += count(array_filter(array_slice($value, 2, 30)));

                $models = array_merge(
                    $this->newModels($value, self::BUY),
                    $this->newModels($value, self::SELL)
                );

                $insert = array_merge($insert, $models);

                if ($i % 100 == 0) {
                    $this->info($i);
                }
            }
        } catch (\Exception $e) {
            throw new StockException($e, $code);
        }

        $this->info('date: ' . $this->date);

        // 批次寫入資料
        try {
            $this->repo->beginTransaction();

            foreach (collect($insert)->chunk(5000)->toArray() as $value) {
                if ($this->repo->batchInsert($value)) {
                    $saveMainTotal += count($value);
                    $this->info('main total: ' . $mainTotal . ' save main total: ' . $saveMainTotal);
                } else {
                    throw new \Exception('save error', $saveMainTotal);
                }
            }

            $this->repo->commit();

            $this->info('result total: ' . $mainTotal . ' save total: ' . $saveMainTotal);
        } catch (\Exception $e) {
            $this->repo->rollBack();
            Log::error("rollBack: " . $e->getMessage());
        }

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

            if ($stock[62] > 0) {
                $volumeRatio = round(($model['count'] / $stock[62]) * 100, 2);
            } else {
                $volumeRatio = 0.00;
            }

            $model['volume_ratio'] = $volumeRatio;

            $models[] = $model;
        }

        return $models;
    }

    /**
     * 已存在主力買賣超分點股票代碼
     *
     * @return array
     */
    private function existCodes(): array
    {
        return $this->repo->codes($this->date)->pluck('code')->all();
    }
}
