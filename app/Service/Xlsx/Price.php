<?php

namespace App\Service\Xlsx;

class Price extends Xlsx
{
    /**
     * @return string
     */
    protected function getDataPath(): string
    {
        return $this->path . '\price\\' . $this->name();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'price_' . $this->date . '.xlsx';
    }
}
