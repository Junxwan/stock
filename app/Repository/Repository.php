<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class Repository
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * Repository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function beginTransaction()
    {
        $this->model->newQuery()->getConnection()->beginTransaction();
    }

    public function commit()
    {
        $this->model->newQuery()->getConnection()->commit();
    }

    public function rollBack()
    {
        $this->model->newQuery()->getConnection()->rollBack();
    }

    /**
     * @param array $models
     *
     * @return bool
     */
    public function batchInsert(array $models): bool
    {

        return $this->model->newQuery()->insert($models);
    }
}
