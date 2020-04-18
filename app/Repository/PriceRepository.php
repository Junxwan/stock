<?php

namespace App\Repository;

class PriceRepository extends Repository
{
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
