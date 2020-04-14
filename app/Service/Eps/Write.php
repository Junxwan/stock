<?php

namespace App\Service\Eps;

use App\Service\Xlsx;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Style\Style as StyleColumn;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Write extends Xlsx
{
    /**
     * @param mixed $sheet
     * @param Worksheet $worksheet
     * @param int $index
     * @param Collection $price
     * @param Collection $eps
     * @param mixed $value
     *
     * @return mixed|void
     */
    protected function cellColumnValue(
        $sheet,
        Worksheet $worksheet,
        int $index,
        Collection $price,
        Collection $eps,
        $value
    ) {
        // TODO: Implement cellColumnValue() method.
    }

    /**
     * @param mixed $sheet
     * @param StyleColumn $style
     * @param string $column
     * @param mixed $value
     */
    protected function cellStyle($sheet, StyleColumn $style, string $column, $value)
    {
        // TODO: Implement cellStyle() method.
    }
}
