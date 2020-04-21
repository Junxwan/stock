<?php

namespace App\Repository;

use App\Model\KeyResult;

class KeyResultRepository extends Repository
{
    /**
     * KeyResultRepository constructor.
     *
     * @param KeyResult $model
     */
    public function __construct(KeyResult $model)
    {
        parent::__construct($model);
    }
}
