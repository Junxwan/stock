<?php

namespace App\Service\Import\Year;

class ClosePrice extends Import
{
    /**
     * @return string
     */
    protected function key()
    {
        return 'close';
    }
}
