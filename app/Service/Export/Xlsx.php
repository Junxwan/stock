<?php

namespace App\Service\Export;

use App\Exceptions\StockException;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Log;
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
     * @var Data
     */
    private $data;

    /**
     * Xlsx constructor.
     *
     * @param Data $data
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(Data $data)
    {
        $this->data = $data;
        $this->output = app()->make(
            OutputStyle::class, ['input' => new ArgvInput(), 'output' => new ConsoleOutput()]
        );
    }

    /**
     * 建立分析結果名單
     *
     * @param array $sheets
     * @param string $date
     * @param string $path
     */
    public function save(array $sheets, string $date, string $path)
    {
        $example = $path . '\example.xlsx';
        $file = $path . '\\' . $date . '.xlsx';

        try {
            $this->data->init($date);
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
            $s = app($sheet);
            $this->info("create " . $s->name() . " list.....");
            $s->put($spreadsheet, $sheet, $this->data);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }
}
