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
        $this->info("read " . $this->name() . ' ....');
        $spreadsheet = IOFactory::load($this->getDataPath());
        $data = collect($spreadsheet->getSheet($this->index())->toArray());
        foreach ($this->removeIndexs as $index) {
            unset($data[$index]);
        }
        return $data;
    }

    /**
     * 檔案路徑
     *
     * @return string
     */
    protected abstract function getDataPath(): string;

    /**
     * 檔案簡稱
     */
    public abstract function name(): string;

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
     * @return int
     */
    protected function index(): int
    {
        return 0;
    }
}
