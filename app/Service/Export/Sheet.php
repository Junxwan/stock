<?php

namespace App\Service\Export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

interface Sheet
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @param Spreadsheet $spreadsheet
     * @param Sheet $sheet
     * @param string $date
     *
     * @return bool
     */
    public function put(Spreadsheet $spreadsheet, Sheet $sheet, Data $data): bool;
}
