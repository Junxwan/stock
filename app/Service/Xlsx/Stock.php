<?php

namespace App\Service\Xlsx;

class Stock extends Xlsx
{
    /**
     * @return \Illuminate\Support\Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getData()
    {
        $data = parent::getData();
        unset($data[0]);
        return $data;
    }

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
