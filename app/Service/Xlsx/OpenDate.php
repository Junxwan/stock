<?php

namespace App\Service\Xlsx;

class OpenDate extends Xlsx
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
        return $this->path . '\\' . $this->name();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->year . '.xlsx';
    }
}
