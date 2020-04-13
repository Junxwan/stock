<?php

namespace App\Service;

use Carbon\Carbon;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Writer;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class Xlsx
{
    use InteractsWithIO;

    /**
     * @var \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    private $spreadsheet;

    /**
     * @var Collection
     */
    private $price;

    /**
     * @var Collection
     */
    private $futures;

    /**
     * @var Collection
     */
    private $highEndShipping;

    /**
     * @var Collection
     */
    private $eps;

    /**
     * 普通欄位
     *
     * @var array
     */
    protected $column = [];

    /**
     * Xlsx constructor.
     *
     * @param Data $data
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(Data $data)
    {
        $this->price = $data->getPrice();
        $this->futures = $data->getFutures();
        $this->highEndShipping = $data->getHighEndShipping();
        $this->eps = $data->getEps();

        $this->output = app()->make(
            OutputStyle::class, ['input' => new ArgvInput(), 'output' => new ConsoleOutput()]
        );
    }

    /**
     * 建立分析結果名單
     *
     * @param array $sheets
     * @param string $file
     * @param string $example
     */
    public function save(array $sheets, string $file, string $example)
    {
        try {
            $writer = new Writer($this->createSpreadsheet($sheets, $example));
            $writer->save($file);
        } catch (\Exception $e) {
            Log::error($e->getTraceAsString());
            $this->error($e->getTraceAsString());
        }
    }

    /**
     * 建立結果sheet
     *
     * @param array $sheets
     * @param string $example
     *
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function createSpreadsheet(array $sheets, string $example)
    {
        $spreadsheet = IOFactory::load($example);
        foreach ($sheets as $sheet) {
            if ($sheet instanceof sheet) {
                $this->info("create " . $sheet->type() . " list.....");
                $this->putSheet($spreadsheet, $sheet);
            }
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * 建立所有個股分析結果名單
     *
     * @param Spreadsheet $spreadsheet
     * @param Sheet $sheet
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function putSheet(Spreadsheet $spreadsheet, Sheet $sheet)
    {
        $index = 1;

        $s = $spreadsheet->getSheet($sheet->index());

        $type = ['buy' => 0, 'sell' => 1, 'all' => 2];

        foreach ($sheet->getData() as $i => $value) {
            $p = collect($this->price->where("code", $value["code"])->first());
            $a = collect($this->dangchong->where("code", $value["code"])->first());
            $o = collect($this->old->where("code", $value["code"])->where("type", $type[$sheet->type()])->first());
            $e = collect($this->eps->where("code", $value["code"])->first());
            $h = collect($this->highEndShipping->where("code", $value["code"])->first());

            if ($p->isEmpty()) {
                continue;
            }

            if (! $sheet->check($p, $e, $a, $o, $h, $value)) {
                continue;
            }

            $index++;

            foreach ($sheet->data($p, $a, $o, $value) as $k => $v) {
                $this->cellValue($sheet, $s->getCell($k . $index), $v);
            }

            foreach ($sheet->info() as $k => $v) {
                $this->cellValue($sheet, $s->getCell($k . $index), $p->get($v, 0));
            }

            foreach ($sheet->getEpsKeyMap() as $k => $v) {
                $this->cellValue($sheet, $s->getCell($k . $index), $e->get($v, 0));
            }

            foreach ($sheet->main() as $k => $v) {
                $this->cellValue($sheet, $s->getCell($k . $index), $a->get($v, 0));
            }

            if ($index % $sheet->outNum() == 0) {
                $this->info($index);
            }
        }

        $sheet->putOther($s, $index);
    }

    /**
     * 設定資料
     *
     * @param Sheet $sheet
     * @param Cell $cell
     * @param mixed $value
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function cellValue(Sheet $sheet, Cell $cell, $value)
    {
        $cell->setValue($value);
        $style = $cell->getStyle();
        $column = $cell->getColumn();

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

        $style->getFont()->setBold(16);
    }

    /**
     * 日期是否剩下10天內
     *
     * @param string $value
     *
     * @return bool
     */
    private function is10Day(string $value): bool
    {
        $date = Carbon::createFromFormat('Y-m-d', $value);
        $now = Carbon::now();

        if ($date->isBefore($now)) {
            return false;
        }

        return $date->diff($now)->d <= 10;
    }
}
