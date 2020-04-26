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
     * @param array $parameter
     */
    public static function save(string $path, string $date, string $type, array $parameter = [])
    {
        $xlsxNamespace = 'App\Service\Xlsx\\';
        if (isset($parameter['year']) && $parameter['year']) {
            $xlsxNamespace = 'App\Service\Xlsx\Year\\';
        }

        $importNamespace = 'App\Service\Import\\';
        if (isset($parameter['year']) && $parameter['year']) {
            $importNamespace = 'App\Service\Import\Year\\';
        }

        $import = app($importNamespace . $type, [
            'xlsx' => app($xlsxNamespace . $type, [
                'path' => $path,
                'date' => $date,
            ]),
        ]);

        $import->write();
    }
}
