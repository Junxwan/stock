<?php

/**
 * 匯入主力買賣超分點
 */

namespace App\Service\Import;

use App\Exceptions\StockException;
use App\Repository\MainRepository;
use App\Repository\StockRepository;
use App\Service\Arr;
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
     * @var StockRepository
     */
    private $stockRepo;

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
        $this->stockRepo = app(StockRepository::class, [
            'model' => app(\App\Model\Stock::class),
        ]);

        parent::__construct($xlsx);
    }

    /**
     * 寫入主力買賣超分點
     *
     * @param Collection $data
     *
     * @return bool
     * @throws StockException
     */
    protected function insert(Collection $data): bool
    {
        $codes = Arr::key($this->repo->codes($this->date)->toArray(), 'code');

        // 撿查當前資料庫中股票清單與檔案中的股票是否有落差
        $diff = array_diff(
            $data->groupBy('0')->keys()->toArray(),
            $this->stockRepo->all()->pluck('code')->toArray()
        );

        if (count($diff) != 0) {
            $this->error("diff code: " . implode(',', $diff));
            return false;
        }

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

            $models[] = $model;
        }

        return $models;
    }
}
