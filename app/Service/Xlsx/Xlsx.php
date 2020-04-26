<?php

namespace App\Service\Xlsx;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

abstract class Xlsx
{
    use InteractsWithIO;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $date;

    /**
     * @var string
     */
    protected $year;

    /**
     * @var array
     */
    protected $removeIndexs = [];

    /**
     * Xlsx constructor.
     *
     * @param string $path
     * @param string $date
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(string $path, string $date = '')
    {
        $this->path = $path;

        if ($date == 'now') {
            $this->date = date('Y-m-d');
            $this->year = date('Y');
        } elseif (strlen($date) == 4) {
            $t = Carbon::createFromFormat('Y', $date);
            $this->date = $t->format('Y-m-d');
            $this->year = $t->year;
        } else {
            $this->date = $date;
            $this->year = Carbon::createFromFormat('Y-m-d', $this->date)->year;
        }

        $this->output = app()->make(
            OutputStyle::class, ['input' => new ArgvInput(), 'output' => new ConsoleOutput()]
        );
    }

    /**
     * execl檔案
     *
     * @return \Illuminate\Support\Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function getData()
    {
        $spreadsheet = $this->getSpreadsheet();
        $index = $this->index();

        if (is_array($index)) {
            $data = collect();
            foreach ($index as $i => $k) {
                $d = collect($spreadsheet->getSheet($i)->toArray());

                foreach ($this->removeIndexs as $c) {
                    unset($d[$c]);
                }

                $data->put($k, $d);
            }
        } else {
            $data = collect($spreadsheet->getSheet($index)->toArray());
            foreach ($this->removeIndexs as $index) {
                unset($data[$index]);
            }

            // 最後一筆資料如果是統計筆數則移除
            if ($data->last()[1] == null) {
                $data->pop();
            }
        }

        return $data;
    }

    /**
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function getSpreadsheet()
    {
        $this->info("read " . $this->name() . ' ....');
        return IOFactory::load($this->getDataPath());
    }

    /**
     * 檔案路徑
     *
     * @return string
     */
    protected function getDataPath(): string
    {
        return $this->path . '\\' . $this->name();
    }

    /**
     * 檔案簡稱
     *
     * @return string
     */
    public function name(): string
    {
        return $this->year . '.xlsx';
    }

    /**
     * @return string
     */
    public function date()
    {
        return $this->date;
    }

    /**
     * @return int|string
     */
    public function year()
    {
        return $this->year;
    }

    /**
     * Sheet index
     *
     * @return mixed
     */
    protected function index()
    {
        return 0;
    }
}
