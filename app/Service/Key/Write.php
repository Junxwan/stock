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
        $columns = $sheet->getColumns();
        $this->columnStyles = array_merge(
            $columns->get('style'),
            $columns->get('slope'),
            [$columns->get('bandwidth')]
        );

        $worksheet = $spreadsheet->getSheet($sheet->index());

        try {
            $index = 1;
            $code = '';
            foreach ($sheet->getData() as $i => $value) {
                $code = $value["code"];

                if (! $sheet->check($value)) {
                    continue;
                }

                $index++;

                if (! $this->cellColumnValue($sheet, $worksheet, $index, $value)) {
                    $index--;
                    continue;
                }


                if ($i % $sheet->outNum() == 0) {
                    $this->info($i);
                }
            }

        } catch (\Exception $e) {
            throw new StockException($e, $code);
        }
    }

    /**
     * @param Sheet $sheet
     * @param Worksheet $worksheet
     * @param int $index
     * @param mixed $value
     *
     * @return bool
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function cellColumnValue(Sheet $sheet, Worksheet $worksheet, int $index, $value): bool
    {
        $price = collect($this->price->where("code", $value["code"])->first());
        $eps = collect($this->eps->where("code", $value["code"])->first());

        if ($price->isEmpty()) {
            return false;
        }

        foreach ($sheet->putData($value) as $k => $v) {
            $this->cellValue($sheet, $worksheet->getCell($k . $index), $v);
        }

        foreach ($sheet->getColumns()->get('info') as $k => $v) {
            $this->cellValue($sheet, $worksheet->getCell($k . $index), $price->get($v, 0));
        }

        foreach ($sheet->getColumns()->get('eps') as $k => $v) {
            $this->cellValue($sheet, $worksheet->getCell($k . $index), $eps->get($v, 0));
        }

        return true;
    }

    /**
     * @param Sheet $sheet
     * @param StyleColumn $style
     * @param string $column
     * @param mixed $value
     */
    protected function cellStyle(Sheet $sheet, StyleColumn $style, string $column, $value)
    {
        $columns = $sheet->getColumns();

        if ($column == 'A') {
            if ($this->futures->where('code', $value)->isNotEmpty()) {
                $style->getFont()->getColor()->setRGB("974706");
            }
        }

        $this->setColumnColor($style, $column, $this->columnStyles, $value);

        if ($column == $columns['main']) {
            if ($sheet->type() == "buy") {
                Style::setStyleRed($style);
            }
            if ($sheet->type() == "sell") {
                Style::setStyleGreen($style);
            }
        }

        if ($column == $columns['bandwidth']) {
            if ($value >= 20) {
                Style::setStyleDeepRed($style);
            } elseif ($value >= 1 && $value <= 5) {
                Style::setStyleDeepGreen($style);
            }
        }

        if (in_array($column, $columns['slope'])) {
            if ($value >= 1) {
                Style::setStyleDeepRed($style);
            } elseif ($value <= -1) {
                Style::setStyleDeepGreen($style);
            }
        }

        if ($column == $columns['high_stray']) {
            if ($value >= 50) {
                Style::setStyleRed($style);
            } else {
                Style::setStyleGreen($style);
            }
        }

        if ($column == $columns['financing_maintenance'] && $value <= 130) {
            Style::setStyleGreen($style);
        }

        if ($column == $columns['financing_use'] && $value >= 10) {
            Style::setStyleRed($style);
        }

        if ($column == $columns['net_worth'] && $value <= 0.5) {
            Style::setStyleGreen($style);
        }

        if ($column == $columns['securities_ratio'] && $value >= 25) {
            Style::setStyleDeepRed($style);
        }

        $this->setColumnColorByDate($style, $column, $columns['date'], $value);

        if ($column == $columns['volume_20_multiple'] && $value >= 2) {
            Style::setStyleDeepRed($style);
        }
    }
}
