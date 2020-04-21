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
}
