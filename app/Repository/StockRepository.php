<?php

namespace App\Repository;

use App\Model\Stock;

class StockRepository extends Repository
{
    public function __construct(Stock $model)
    {
        parent::__construct($model);
    }
}
