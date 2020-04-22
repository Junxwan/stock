<?php

namespace App\Service\Eps;

use App\Exceptions\StockException;
use App\Service\Sheet;
use App\Service\Xlsx;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Style as StyleColumn;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Write extends Xlsx
{
    /**
     * @var Collection
     */
    private $eps;

    /**
     * Write constructor.
     *
     * @param Data $data
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(Data $data)
    {
        $this->eps = $data->getEps();
        parent::__construct();
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param Sheet $sheet
     *
     * @throws StockException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function putSheet(Spreadsheet $spreadsheet, Sheet $sheet)
    {
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
     */
    protected function cellColumnValue(Sheet $sheet, Worksheet $worksheet, int $index, $value): bool
    {

    }

    /**
     * @param Sheet $sheet
     * @param StyleColumn $style
     * @param string $column
     * @param mixed $value
     */
    protected function cellStyle(Sheet $sheet, StyleColumn $style, string $column, $value)
    {
    }
}
