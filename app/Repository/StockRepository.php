<?php

namespace App\Repository;

class StockRepository extends Repository
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->model->newQuery()->get();
    }
}
