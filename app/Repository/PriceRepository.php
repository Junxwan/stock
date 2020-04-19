<?php

namespace App\Repository;

use App\Model\Price;

class PriceRepository extends Repository
{
    /**
     * PriceRepository constructor.
     *
     * @param Price $model
     */
    public function __construct(Price $model)
    {
        parent::__construct($model);
    }

    /**
     * 某日所有股票
     *
     * @param string $date
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function date(string $date)
    {
        return $this->model->newQuery()->where('date', $date)->get();
    }
}
