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
}
