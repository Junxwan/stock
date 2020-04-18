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
        if ($date == 'now') {
            $date = date('Y-m-d');
        }

        $xlsx = app('App\Service\Xlsx\\' . $type, [
            'path' => $path,
            'date' => $date,
        ]);

        $repos = app('App\Repository\\' . $type . 'Repository', [
            'model' => app('App\Model\\' . $type),
        ]);

        $import = app('App\Service\Import\\' . $type, [
            'repo' => $repos,
            'xlsx' => $xlsx,
        ]);

        $import->write();
    }
}
