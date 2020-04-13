<?php

namespace App\Service;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class Price
{
    use InteractsWithIO;

    /**
     * @var string
     */
    private $date;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $pricePath;

    /**
     * @var Collection
     */
    private $price;

    /**
     * Price constructor.
     *
     * @param string $path
     * @param string $date
     */
    public function __construct(string $path, string $date)
    {
        $this->date = $date;
        $this->path = $path;
        $this->pricePath = [
            'q1' => $path . '\price\year\\' . $date . 'Q1.xlsx',
            'q2' => $path . '\price\year\\' . $date . 'Q2.xlsx',
            'q3' => $path . '\price\year\\' . $date . 'Q3.xlsx',
            'q4' => $path . '\price\year\\' . $date . 'Q4.xlsx',
        ];

        $this->output = app()->make(
            OutputStyle::class, ['input' => new ArgvInput(), 'output' => new ConsoleOutput()]
        );
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function read()
    {
        foreach ($this->pricePath as $key => $path) {
            if (! File::exists($path)) {
                continue;
            }

            $this->info($this->date . ' ' . $key . " eps file: " . $path);

            $spreadsheet = IOFactory::load($path);
            $data = $spreadsheet->getActiveSheet()->toArray();

            unset($data[0][0]);
            unset($data[0][1]);
            unset($date);

            foreach ($data[0] as $value) {
                $date[] = substr($value, 0, 8);
            }

            unset($data[0]);
            unset($p);

            foreach ($data as $prices) {
                foreach (array_slice($prices, 2) as $i => $price) {
                    $p[$date[$i]] = $price;
                }

                $result[$key][$prices[0]] = isset($p) ? $p : [];
            }
        }

        $res = [];
        $qs = array_keys($result);
        foreach ($data as $value) {
            foreach ($qs as $q) {
                if (isset($res[$value[0]])) {
                    $res[$value[0]] = $res[$value[0]] + $result[$q][$value[0]];
                } else {
                    $res[$value[0]] = $result[$q][$value[0]];
                }
            }
        }

        $this->price = collect($res);
    }

    /**
     * @return Collection
     */
    public function getPrice(): Collection
    {
        return $this->price;
    }
}
