<?php

namespace App\Repository;


use App\Model\IndustryClassification;

class IndustryClassificationRepository extends Repository
{
    /**
     * IndustryClassificationRepository constructor.
     *
     * @param IndustryClassification $model
     */
    public function __construct(IndustryClassification $model)
    {
        parent::__construct($model);
    }

    /**
     * 全部資料
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->model->newQuery()->get();
    }
}
