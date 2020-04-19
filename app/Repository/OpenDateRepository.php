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
}
