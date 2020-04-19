<?php

namespace App\Repository;

use App\Model\Main;

class MainRepository extends Repository
{
    /**
     * MainRepository constructor.
     *
     * @param Main $model
     */
    public function __construct(Main $model)
    {
        parent::__construct($model);
    }

    /**
     * @param string $date
     *
     * @return \Illuminate\Support\Collection
     */
    public function codes(string $date)
    {
        return $this->model->newQuery()
            ->where("date", $date)
            ->distinct()
            ->select('code')
            ->get();
    }
}
