<?php

namespace App\Service;

class Arr
{
    /**
     * @param array $data
     * @param string $key
     *
     * @return string
     */
    public static function string(array $data, string $key): string
    {
        return implode(',', \Illuminate\Support\Arr::pluck($data, $key));
    }
}
