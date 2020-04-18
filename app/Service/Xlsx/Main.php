<?php

namespace App\Service\Xlsx;

class Main extends Xlsx
{
    /**
     * @return string
     */
    protected function getDataPath(): string
    {
        return $this->path . '\main\\' . $this->name();
    }

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
    public function name(): string
    {
        return 'main_' . $this->date . '.xlsx';
    }
}
