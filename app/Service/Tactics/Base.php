<?php

namespace App\Service\Tactics;

abstract class Base
{
    /**
     * @param string $type
     *
     * @return array
     */
    public function param(string $name): array
    {
        return collect($this->methods())->where('name', $name)->first();
    }

    /**
     * @return array
     */
    protected abstract function methods(): array;

    /**
     * @return array
     */
    public abstract function name(): array;
}
