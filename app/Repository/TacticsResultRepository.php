<?php

namespace App\Repository;

use App\Model\TacticsResult;

class TacticsResultRepository extends Repository
{
    /**
     * TacticsResultRepository constructor.
     *
     * @param TacticsResult $model
     */
    public function __construct(TacticsResult $model)
    {
        parent::__construct($model);
    }

    /**
     * @param string $date
     * @param string $type
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function type(string $date, string $type)
    {
        return $this->model->newQuery()
            ->where('date', $date)
            ->where('type', $type)
            ->get();
    }
}
