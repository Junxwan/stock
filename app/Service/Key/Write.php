<?php

namespace App\Service\Key;

use App\Exceptions\StockException;
use App\Service\Data;
use App\Service\Sheet;
use App\Service\Style;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Style as StyleColumn;
use App\Service\Xlsx;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Write extends Xlsx
{
    /**
     * @var Collection
     */
    protected $price;

    /**
     * @var Collection
     */
    protected $futures;

    /**
     * @var Collection
     */
    protected $eps;

    /**
     * @var array
     */
    private $columnStyles;

    /**
     * Write constructor.
     *
     * @param Data $data
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(Data $data)
    {
        $this->price = $data->getPrice();
        $this->futures = $data->getFutures();
        $this->eps = $data->getEps();

        parent::__construct();
    }

    /**
     * 建立所有個股分析結果名單
     *
     * @param Spreadsheet $spreadsheet
     * @param Sheet $sheet
     *
     * @throws StockException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function putSheet(Spreadsheet $spreadsheet, Sheet $sheet)
    {
        $this->columnStyles = array_merge(
            $sheet->gets(),
            $sheet->slope(),
            [$sheet->bandwidth()]
        );

        $index = 1;

        $worksheet = $spreadsheet->getSheet($sheet->index());

        try {
            $code = '';
            foreach ($sheet->getData() as $i => $value) {
                $code = $value["code"];

                if (! $sheet->check($value)) {
                    continue;
                }

                $index++;

                $this->cellColumnValue($sheet, $worksheet, $index, $value);

                if ($index % $sheet->outNum() == 0) {
                    $this->info($index);
                }
            }

        } catch (\Exception $e) {
            throw new StockException($e, $code);
        }

        $sheet->putOther($worksheet, $index);
    }

    /**
     * @param Column|mixed $sheet
     * @param Worksheet $worksheet
     * @param int $index
     * @param mixed $value
     *
     * @return mixed|void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function cellColumnValue($sheet, Worksheet $worksheet, int $index, $value)
    {
        $price = collect($this->price->where("code", $value["code"])->first());
        $eps = collect($this->eps->where("code", $value["code"])->first());

        if ($price->isEmpty()) {
            return;
        }

        foreach ($sheet->data($price, $value) as $k => $v) {
            $this->cellValue($sheet, $worksheet->getCell($k . $index), $v);
        }

        foreach ($sheet->infoDataColumn() as $k => $v) {
            $this->cellValue($sheet, $worksheet->getCell($k . $index), $price->get($v, 0));
        }

        foreach ($sheet->epsDataColumn() as $k => $v) {
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

        $this->setColumnColor($style, $column, $this->columnStyles, $value);

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

        $this->setColumnColorByDate($style, $column, $sheet->date(), $value);

        if ($column == $sheet->volume20Multiple() && $value >= 2) {
            Style::setStyleDeepRed($style);
        }
    }
}
