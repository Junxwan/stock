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
     * @var array
     */
    private $param;

    /**
     * Xlsx constructor.
     *
     * @param string $path
     * @param string $date
     * @param array $param
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(string $path, string $date = '', array $param = [])
    {
        $this->path = $path;
        $this->param = $param;

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
        if (! $this->isJson()) {
            return $this->readSpreadsheet($this->getDataPath());
        }

        return $this->readJson($this->getDataPath());
    }

    /**
     * @param array $name
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllData(array $names)
    {
        $data = collect();
        foreach ($names as $n) {
            $path = $this->path . '\\' . $n . '\\' . $this->name();

            if ($this->isJson()) {
                $d = $this->readJson($path);
            } else {
                $d = $this->readSpreadsheet($path);
            }

            $data->put($n, $d);
        }

        return $data;
    }

    /**
     * @param string $path
     *
     * @return \Illuminate\Support\Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function readSpreadsheet(string $path)
    {
        $index = $this->index();
        $spreadsheet = $this->getSpreadsheet($path);

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
     * @param string $path
     *
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    protected function readJson(string $path)
    {
        $index = $this->index();
        $jsonFile = file_get_contents($path);

        if ($jsonFile == '') {
            throw new \Exception($path . ' is not exist');
        }

        $json = json_decode($jsonFile, true);

        if (is_array($index)) {
            $data = collect();

            foreach ($index as $k) {
                $d = $json[$k];

                foreach ($this->removeIndexs as $c) {
                    unset($d[$c]);
                }

                $data->put($k, $d);
            }
        } else {
            $data = collect($json);
            foreach ($this->removeIndexs as $index) {
                unset($data[$index]);
            }

            // 最後一筆資料如果是統計筆數則移除
            if (count($data->last()) == 1) {
                $data->pop();
            }
        }

        return $data;
    }

    /**
     * @param string $path
     *
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    protected function getSpreadsheet(string $path)
    {
        $this->info("read " . $this->name() . ' ....');
        return IOFactory::load($path);
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
        if ($this->isJson()) {
            return $this->year . '.json';
        }

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

    /**
     *
     * @return bool
     */
    protected function isJson()
    {
        return isset($this->param['json']) && $this->param['json'];
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getParam(string $key)
    {
        return $this->param[$key];
    }
}
