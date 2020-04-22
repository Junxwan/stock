<?php

namespace App\Service\Export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Buy extends Write
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'buy';
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param Sheet $sheet
     * @param string $date
     *
     * @return mixed|void
     */
    protected function put(Spreadsheet $spreadsheet, Sheet $sheet, Data $data)
    {
        $data->price();
    }
}
