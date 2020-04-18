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
     * @return string
     */
    public function name(): string
    {
        return 'main_' . $this->date . '.xlsx';
    }
}
