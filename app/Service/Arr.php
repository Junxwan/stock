<?php

namespace App\Service;

class Arr
{
    /**
     * @param array $data
     * @param string $key
     *
     * @return array
     */
    public static function key(array $data, string $key): array
    {
        $d = [];
        foreach ($data as $v) {
            $d[$v[$key]] = $data;
        }

        return $d;
    }

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
