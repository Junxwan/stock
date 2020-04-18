<?php

namespace App\Repository;

class IndustryClassificationRepository extends Repository
{
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
