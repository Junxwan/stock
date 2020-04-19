<?php

namespace App\Repository;

use App\Model\Point;

class PointRepository extends Repository
{
    /**
     * PointRepository constructor.
     *
     * @param Point $model
     */
    public function __construct(Point $model)
    {
        parent::__construct($model);
    }
}
