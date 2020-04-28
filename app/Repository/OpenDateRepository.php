<?php

namespace App\Repository;

use App\Model\OpenDate;

class OpenDateRepository extends Repository
{
    /**
     * OpenDateRepository constructor.
     *
     * @param OpenDate $model
     */
    public function __construct(OpenDate $model)
    {
        parent::__construct($model);
    }

    /**
     * @param string $year
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function year(string $year)
    {
        return $this->model->newQuery()
            ->whereBetween('date', [$year . '-01-01', $year . '-12-31'])
            ->where('open', true)
            ->orderByDesc('date')
            ->get();
    }

    /**
     * @param string $date
     *
     * @return \Illuminate\Database\Eloquent\Model|object|null
     */
    public function yesterday(string $date)
    {
        return $this->model->newQuery()
            ->where('date', "<", $date)
            ->where('open', true)
            ->orderByDesc('date')
            ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->model->newQuery()
            ->where('open', true)
            ->orderByDesc('date')
            ->get();
    }
}
