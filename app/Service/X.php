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
use PhpOffice\PhpSpreadsheet\Style\Style as StyleColumn;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Writer;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

abstract class X
{
    use InteractsWithIO;

    /**
     * @var \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    private $spreadsheet;

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
    protected $highEndShipping;

    /**
     * @var Collection
     */
    protected $eps;

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

        $worksheet = $spreadsheet->getSheet($sheet->index());

        foreach ($sheet->getData() as $i => $value) {
            $p = collect($this->price->where("code", $value["code"])->first());
            $e = collect($this->eps->where("code", $value["code"])->first());

            if ($p->isEmpty()) {
                continue;
            }

            if (! $sheet->check($p, $e, $value)) {
                continue;
            }

            $index++;

            $this->cellColumnValue($sheet, $worksheet, $index, $p, $e, $value);


            if ($index % $sheet->outNum() == 0) {
                $this->info($index);
            }
        }

        $sheet->putOther($worksheet, $index);
    }

    /**
     * @param mixed $sheet
     * @param Worksheet $worksheet
     * @param int $index
     * @param Collection $price
     * @param Collection $eps
     * @param mixed $value
     *
     * @return mixed
     */
    abstract protected function cellColumnValue(
        $sheet,
        Worksheet $worksheet,
        int $index,
        Collection $price,
        Collection $eps,
        $value
    );

    /**
     * 設定資料
     *
     * @param mixed $sheet
     * @param StyleColumn $style
     * @param string $column
     * @param mixed $value
     */
    abstract protected function cellStyle($sheet, StyleColumn $style, string $column, $value);

    /**
     * 設定資料
     *
     * @param mixed $sheet
     * @param Cell $cell
     * @param mixed $value
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function cellValue($sheet, Cell $cell, $value)
    {
        $cell->setValue($value);
        $style = $cell->getStyle();
        $column = $cell->getColumn();
        $this->cellStyle($sheet, $style, $column, $value);
        $style->getFont()->setBold(16);
    }


    /**
     * 日期是否剩下10天內
     *
     * @param string $value
     *
     * @return bool
     */
    protected function is10Day(string $value): bool
    {
        $date = Carbon::createFromFormat('Y-m-d', $value);
        $now = Carbon::now();

        if ($date->isBefore($now)) {
            return false;
        }

        return $date->diff($now)->d <= 10;
    }
}
