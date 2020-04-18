<?php

namespace App\Service\Xlsx;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
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
     * Xlsx constructor.
     *
     * @param string $path
     * @param string $date
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(string $path, string $date)
    {
        $this->path = $path;
        $this->date = $date;

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
        $this->info("read " . $this->name() . '....');
        $spreadsheet = IOFactory::load($this->getDataPath());
        return collect($spreadsheet->getSheet($this->index())->toArray());
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
     * Sheet index
     *
     * @return int
     */
    protected function index(): int
    {
        return 0;
    }
}
