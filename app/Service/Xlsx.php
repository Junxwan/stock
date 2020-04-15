<?php

namespace App\Service;

use App\Exceptions\StockException;
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

abstract class Xlsx
{
    use InteractsWithIO;

    /**
     * @var \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    private $spreadsheet;

    /**
     * Xlsx constructor.
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct()
    {
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
        } catch (StockException $e) {
            Log::error('code: ' . $e->getCode() . ' ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            $this->error($e->getTraceAsString());
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
     * @return Spreadsheet
     * @throws StockException
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
     * @throws StockException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected abstract function putSheet(Spreadsheet $spreadsheet, Sheet $sheet);

    /**
     * @param mixed $sheet
     * @param Worksheet $worksheet
     * @param int $index
     * @param mixed $value
     *
     * @return mixed
     */
    abstract protected function cellColumnValue($sheet, Worksheet $worksheet, int $index, $value);

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
     * @param StyleColumn $style
     * @param string $column
     * @param array $columnStyles
     * @param mixed $value
     */
    protected function setColumnColor(StyleColumn $style, string $column, array $columnStyles, $value)
    {
        if (in_array($column, $columnStyles)) {
            if ($value > 0) {
                Style::setStyleRed($style);
            }
            if ($value < 0) {
                Style::setStyleGreen($style);
            }
        }
    }

    /**
     * @param StyleColumn $style
     * @param string $column
     * @param array $columnStyles
     * @param $value
     */
    public function setColumnColorByDate(StyleColumn $style, string $column, array $columnStyles, $value)
    {
        if (in_array($column, $columnStyles)) {
            if ($value != '' && $this->is10Day($value)) {
                Style::setStyleDeepRed($style);
            }
        }
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
