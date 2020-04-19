<?php

namespace App\Service\Import;

use App\Repository\PointRepository;
use App\Service\Xlsx\Xlsx;
use Illuminate\Support\Collection;
use \App\Service\Xlsx\Point as Type;

class Point extends Import
{
    /**
     * 普通分點
     */
    const GENERAL = 0;

    /**
     * 外資
     */
    const FOREIGN = 1;

    /**
     * 官股
     */
    const OFFICIAL = 2;

    /**
     * 經記部
     */
    const JINGJI = 3;

    /**
     * 分類
     *
     * @var int[]
     */
    private $type = [
        Type::ALL => self::GENERAL,
        Type::FOREIGN => self::FOREIGN,
        Type::OFFICIAL => self::OFFICIAL,
        Type::JINGJI => self::JINGJI,
    ];

    /**
     * @var PointRepository
     */
    private $repo;

    /**
     * Point constructor.
     *
     * @param PointRepository $pointRepo
     * @param Xlsx $xlsx
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(PointRepository $pointRepo, Xlsx $xlsx)
    {
        $this->repo = $pointRepo;
        parent::__construct($xlsx);
    }

    /**
     * @param Collection $insert
     *
     * @return bool
     */
    protected function insert(Collection $data): bool
    {
        $codes = $this->repo->all()->pluck('code')->toArray();
        $insert = [];

        foreach ($data->toArray() as $key => $item) {
            foreach ($item as $value) {
                if ($value[0] == 0 || in_array($value[0], $codes)) {
                    continue;
                }

                if (isset($insert[$value[0]])) {
                    if ($insert[$value[0]]['type'] < $this->type[$key]) {
                        $insert[$value[0]]['type'] = $this->type[$key];
                    }
                    continue;
                }

                $insert[$value[0]] = [
                    'code' => $value[0],
                    'name' => implode('', explode(' ', $value[1])),
                    'type' => $this->type[$key],
                ];
            }
        }

        $result = true;
        if (count($insert) > 0) {
            $result = $this->repo->batchInsert($insert);
        }

        $this->info('====================================================');
        $this->info('save total: ' . count($insert));
        $this->info('code: ' . implode(',', $insert));

        return $result;
    }
}
