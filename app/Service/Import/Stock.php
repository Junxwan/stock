<?php

/**
 * 載入股票清單
 */

namespace App\Service\Import;

use App\Model\IndustryClassification;
use App\Repository\IndustryClassificationRepository;
use App\Repository\StockRepository;
use App\Service\Arr;
use App\Service\Xlsx\Xlsx;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Stock extends Import
{
    /**
     * @var StockRepository
     */
    private $repo;

    /**
     * @var IndustryClassificationRepository
     */
    private $industryRepo;

    /**
     * Stock constructor.
     *
     * @param StockRepository $repo
     * @param Xlsx $xlsx
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(StockRepository $repo, Xlsx $xlsx)
    {
        $this->repo = $repo;
        $this->industryRepo = app(IndustryClassificationRepository::class, [
            'model' => app(IndustryClassification::class),
        ]);

        parent::__construct($xlsx);
    }

    /**
     * 新增股票清單
     *
     * 0 代碼
     * 1 名稱
     * 2 股本(千)
     * 3 產業名稱
     * 4 產業指數代號
     * 5 產業指數名稱
     * 6 CM細產業分類
     * 7 上市(1) or 上櫃(2)
     * 8 上市日期
     * 9 上櫃日期
     * 10 成立日期
     *
     * @param Collection $data
     *
     * @return bool
     */
    protected function insert(Collection $data): bool
    {
        $this->industryInsert($data);
        $stocks = Arr::key($this->repo->all()->toArray(), 'code');
        $total = 0;
        $insert = [];

        foreach ($data->all() as $i => $value) {
            if (isset($stocks[$value[0]])) {
                continue;
            }

            if ($value[1] == '') {
                continue;
            }

            if ($value[4] == '') {
                $value[4] = null;
            }

            $total++;

            $insert[] = [
                'code' => $value[0],
                'name' => $value[1],
                'capital' => $value[2],
                'industry_code' => $value[4],
                'classification' => $value[6],
                'issued' => $value[7],
                'twse_date' => $this->createDate($value[8]) ?: null,
                'otc_date' => $this->createDate($value[9]) ?: null,
                'creation_date' => $this->createDate($value[10]),
            ];
        }

        if (count($insert) > 1 && $this->repo->batchInsert($insert)) {
            $this->info('stock total: ' . $total . ' insert: ' . count($insert));
            $this->info('stock code: ' . Arr::string($insert, 'code'));
        } else {
            $this->info('stock total: ' . $total . ' insert: 0');
        }

        return true;
    }

    /**
     * 新增產業分類
     *
     * @param Collection $data
     *
     * @return bool
     */
    public function industryInsert(Collection $data): bool
    {
        $industry = [];
        $allIndustry = $this->industryRepo->all();

        $total = 0;
        foreach ($data->all() as $value) {
            $code = $value[4];

            if ($code == '') {
                continue;
            }

            if ($allIndustry->where('code', $code)->isNotEmpty()) {
                continue;
            }

            if (isset($industry[$code])) {
                continue;
            }

            $total++;

            $industry[$code] = [
                'code' => $code,
                'name' => $value[3],
                'tw_name' => $value[5],
            ];
        }

        $result = $this->industryRepo->batchInsert($industry);

        if ($result) {
            $this->info('industry total: ' . $total . ' insert: ' . count($industry));
            $this->info('industry code: ' . Arr::string($industry, 'code'));
        } else {
            $this->info('industry total: ' . $total . ' insert: 0');
        }

        return $result;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function createDate(string $value)
    {
        return $value == '' ? '' : Carbon::createFromFormat('Ymd', $value)->format('Y-m-d');
    }
}
