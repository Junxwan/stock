<?php

namespace App\Repository;

use App\Model\PriceResult;

class PriceResultRepository extends Repository
{
    /**
     * PriceResultRepository constructor.
     *
     * @param PriceResult $model
     */
    public function __construct(PriceResult $model)
    {
        parent::__construct($model);
    }
}
