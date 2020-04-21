<?php

namespace App\Service\Result;

class Facade
{
    /**
     * @param string $date
     * @param string $type
     * @param array $parameter
     */
    public static function save(string $date, string $type, array $parameter = [])
    {
        $result = app('App\Service\Result\\' . $type);
        $result->save($date, $parameter);
    }
}
