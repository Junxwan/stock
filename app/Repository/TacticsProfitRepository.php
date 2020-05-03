<?php

namespace App\Repository;

use App\Model\TacticsProfit;

class TacticsProfitRepository extends Repository
{
    public function __construct(TacticsProfit $model)
    {
        parent::__construct($model);
    }

    /**
     * @param string $startDate
     * @param string $tactics
     * @param string $type
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function type(string $startDate, string $tactics, string $type)
    {
        return $this->model->newQuery()
            ->where('type', $type)
            ->where('tactics', $tactics)
            ->where('start_date', $startDate)
            ->get();
    }
}
