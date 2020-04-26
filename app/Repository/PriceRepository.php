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
     * @param string $code
     * @param string $date
     * @param array $values
     *
     * @return bool
     */
    public function update(string $code, string $date, array $values): bool
    {
        return $this->model->newQuery()
            ->where('code', $code)
            ->where('date', $date)
            ->update($values);
    }

    /**
     * @param string $code
     * @param string $date
     *
     * @return \Illuminate\Database\Eloquent\Model|object|null
     */
    public function get(string $code, string $date)
    {
        return $this->model->newQuery()
            ->where('code', $code)
            ->where('date', $date)
            ->first();
    }
}
