<?php

/**
 * 匯入一年內收盤價
 */

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
