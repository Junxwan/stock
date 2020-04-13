<?php

namespace App\Service\Key;

class Sell extends KeyMain
{
    /**
     * @return int
     */
    public function index(): int
    {
        return 1;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return 'sell';
    }
}
