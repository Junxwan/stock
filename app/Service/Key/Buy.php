<?php

namespace App\Service\Key;

class Buy extends KeyMain
{
    /**
     * @return int
     */
    public function index(): int
    {
        return 0;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return 'buy';
    }
}
