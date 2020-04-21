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
     * @param array $values
     *
     * @return bool
     */
    public function insert(array $values): bool
    {
        return $this->model->newQuery()->insert($values);
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

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->model->newQuery()->get();
    }

    /**
     * 某日資料
     *
     * @param string $date
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function date(string $date)
    {
        return $this->model->newQuery()
            ->where('date', $date)
            ->orderBy('code')
            ->get();
    }
}
