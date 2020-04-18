<?php

namespace App\Service\Xlsx;

class Stock extends Xlsx
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
        return 'stock.xlsx';
    }
}
