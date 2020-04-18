<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

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
