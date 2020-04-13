<?php

namespace App\Service\Key;

use App\Service\Style;
use PhpOffice\PhpSpreadsheet\Style\Style as StyleColumn;
use App\Service\X;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Write extends X
{
    /**
     * @param Column|mixed $sheet
     * @param Worksheet $worksheet
     * @param int $index
     * @param Collection $price
     * @param Collection $eps
     * @param mixed $value
     *
     * @return mixed|void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function cellColumnValue(
        $sheet,
        Worksheet $worksheet,
        int $index,
        Collection $price,
        Collection $eps,
        $value
    ) {
        foreach ($sheet->data($price, $value) as $k => $v) {
            $this->cellValue($sheet, $worksheet->getCell($k . $index), $v);
        }

        foreach ($sheet->info() as $k => $v) {
            $this->cellValue($sheet, $worksheet->getCell($k . $index), $price->get($v, 0));
        }

        foreach ($sheet->eps() as $k => $v) {
            $this->cellValue($sheet, $worksheet->getCell($k . $index), $eps->get($v, 0));
        }
    }

    /**
     * @param Column|mixed $sheet
     * @param StyleColumn $style
     * @param string $column
     * @param mixed $value
     */
    protected function cellStyle($sheet, StyleColumn $style, string $column, $value)
    {
        if ($column == 'A') {
            if ($this->futures->where('code', $value)->isNotEmpty()) {
                $style->getFont()->getColor()->setRGB("974706");
            }
        }

        $columnStyleKey = array_merge(
            $sheet->gets(),
            $sheet->slope(),
            [$sheet->bandwidth()]
        );

        if (in_array($column, $columnStyleKey)) {
            if ($value > 0) {
                Style::setStyleRed($style);
            }
            if ($value < 0) {
                Style::setStyleGreen($style);
            }
        }

        if ($column == $sheet->mainKey()) {
            if ($sheet->type() == "buy") {
                Style::setStyleRed($style);
            }
            if ($sheet->type() == "sell") {
                Style::setStyleGreen($style);
            }
        }

        if ($column == $sheet->bandwidth()) {
            if ($value >= 20) {
                Style::setStyleDeepRed($style);
            } elseif ($value >= 1 && $value <= 5) {
                Style::setStyleDeepGreen($style);
            }
        }

        if (in_array($column, $sheet->slope())) {
            if ($value >= 1) {
                Style::setStyleDeepRed($style);
            } elseif ($value <= -1) {
                Style::setStyleDeepGreen($style);
            }
        }

        if ($column == $sheet->highStray()) {
            if ($value >= 50) {
                Style::setStyleRed($style);
            } else {
                Style::setStyleGreen($style);
            }
        }

        if ($column == $sheet->financingMaintenance() && $value <= 130) {
            Style::setStyleGreen($style);
        }

        if ($column == $sheet->financingUse() && $value >= 10) {
            Style::setStyleRed($style);
        }

        if ($column == $sheet->netWorth() && $value <= 0.5) {
            Style::setStyleGreen($style);
        }

        if ($column == $sheet->securitiesRatio() && $value >= 25) {
            Style::setStyleDeepRed($style);
        }

        if (in_array($column, $sheet->date())) {
            if ($value != '' && $this->is10Day($value)) {
                Style::setStyleDeepRed($style);
            }
        }

        if ($column == $sheet->volume20Multiple() && $value >= 2) {
            Style::setStyleDeepRed($style);
        }
    }
}
