<?php

/**
 * 匯入一年內開盤價
 */

namespace App\Service\Import\Year;

class OpenPrice extends Import
{
    /**
     * @return string
     */
    protected function key()
    {
        return 'open';
    }
}
