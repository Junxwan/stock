<?php

/**
 * import Facade
 */

namespace App\Service\Import;

class Facade
{
    /**
     * @param string $path
     * @param string $date
     * @param string $type
     */
    public static function save(string $path, string $date, string $type)
    {
        $import = app('App\Service\Import\\' . $type, [
            'xlsx' => app('App\Service\Xlsx\\' . $type, [
                'path' => $path,
                'date' => $date,
            ]),
        ]);

        $import->write();
    }
}
