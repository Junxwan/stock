<?php

namespace App\Service\Xlsx;

class EPS extends Xlsx
{
    /**
     * @var int[]
     */
    protected $removeIndexs = [0];

    /**
     * @return string
     */
    protected function getDataPath(): string
    {
        return $this->path . '\eps\\' . $this->name();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->year . '_eps.xlsx';
    }
}
