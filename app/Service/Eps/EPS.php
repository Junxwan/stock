<?php

namespace App\Service;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class EPS
{
    use InteractsWithIO;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $date;

    /**
     * @var string
     */
    private $epsPath;

    /**
     * @var array
     */
    private $pricePath;

    /**
     * @var Collection
     */
    private $price;

    /**
     * EPS constructor.
     *
     * @param string $path
     * @param string $date
     * @param Price $price
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(string $path, string $date, Price $price)
    {
        $this->path = $path;
        $this->date = $date;
        $this->epsPath = $path . '\eps\\' . $date . '_eps.xlsx';
        $this->price = $price->getPrice();

        $this->output = app()->make(
            OutputStyle::class, ['input' => new ArgvInput(), 'output' => new ConsoleOutput()]
        );
    }

    public function read()
    {
        try {
            $this->info(' ');

            if (! File::exists($this->epsPath)) {
                $this->info($this->epsPath . " is not exists");
                return;
            }

            $this->info("eps file: " . $this->epsPath);

            $this->info("read eps list.....");
            $this->readEps();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            $this->error($e->getTraceAsString());
        }
    }

    /**
     * 0 代碼
     * 1 名稱
     * 2 收盤價
     * 3 q4 eps
     * 4 q3 eps
     * 5 q2 eps
     * 6 q1 eps
     * 7 q1-q3 eps
     * 8 股東會預告-盈餘配股(發放年度)
     * 9 股東會預告-公積配股(發放年度)
     * 10 股東會預告-現金股利(發放年度)
     * 11 股東會預告-股利合計(發放年度) = 盈餘配股 + 公積配股 + 現金股利
     * 12 年度eps
     * 13 股票股利發放率 = (盈餘配股 + 公積配股) / 年度eps
     * 14 現金股利發放率 = 現金股利 / 年度eps
     * 15 股利發放率 = 股利合計 / 年度eps
     * 16 連續N年發放現金股利
     * 17 除權日
     * 18 除息日
     * 19 董事會決議股利派發日
     * 20 1月收盤均價
     * 21 2月收盤均價
     * 22 3月收盤均價
     * 23 4月收盤均價
     * 24 5月收盤均價
     * 25 股利政策-盈餘配股
     * 26 股利政策-公積配股
     * 27 股利政策-股票股利合計 = 盈餘配股 + 公積配股
     * 28 股利政策-盈餘配息
     * 29 股利政策-公積配息
     * 30 股利政策-現金股利 = 盈餘配息 + 公積配息
     * 31 股利政策-年度現金股利 = 盈餘配息 + 公積配息
     * 32 股利政策-年度股利合計 = 股票股利合計 + 年度現金股利
     * 33 上市(1)or上櫃(2)
     * 34 上市日
     * 35 上櫃日
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function readEps()
    {
        $spreadsheet = IOFactory::load($this->epsPath);
        $data = $spreadsheet->getActiveSheet()->toArray();
        unset($data[0]);

        foreach ($data as $item) {
            $month1 = $this->formatInt($item[20]);
            $month2 = $this->formatInt($item[21]);
            $month3 = $this->formatInt($item[22]);
            $month4 = $this->formatInt($item[23]);
            $month5 = $this->formatInt($item[24]);

            $pushPrice = 0;
            if ($item['19'] != '') {
                $pushPrice = $this->price->get($item[0])[$item['19']];
            }

            $res[] = [
                "code" => $item[0],
                "name" => $item[1],
                'eps' => [
                    'q4' => $this->formatInt($item[3]),
                    'q3' => $this->formatInt($item[4]),
                    'q2' => $this->formatInt($item[5]),
                    'q1' => $this->formatInt($item[6]),
                    'q1_q3_eps' => $this->formatInt($item[7]),
                    'year' => $this->formatInt($item[12]),
                ],
                // 股東會預告
                'notice' => [
                    'stock' => [
                        'surplus' => $this->formatInt($item[8]),
                        'provident' => $this->formatInt($item[9]),
                    ],
                    'cash' => $this->formatInt($item[10]),
                    'total' => $this->formatInt($item[11]),
                ],
                // 政策
                'dividend' => [
                    'stock' => [
                        'surplus' => $this->formatInt($item[25]),
                        'provident' => $this->formatInt($item[26]),
                        'total' => $this->formatInt($item[27]),
                    ],
                    'cash' => [
                        'surplus' => $this->formatInt($item[28]),
                        'provident' => $this->formatInt($item[29]),
                        'cash' => $this->formatInt($item[30]),
                        'total' => $this->formatInt($item[31]),
                    ],
                    'total' => $this->formatInt($item[32]),
                ],
                // eps發放率
                'distribution_rate' => [
                    'stock' => $this->formatInt($item[13]),
                    'cash' => $this->formatInt($item[14]),
                    'total' => $this->formatInt($item[15]),
                ],
                'n_rate' => $this->formatInt($item[16]),
                'ex_dividend_day' => $item[17] == "" ? $item[18] : $item[17],
                'resolution_meeting' => $item[19],
                'push' => [
                    'type' => $this->formatInt($item[33]),
                    'market' => $item[34],
                    'otc' => $item[35],
                ],
                'yield' => [
                    'total' => [
                        '1' => $this->yield($month1, $this->formatInt($item[32])),
                        '2' => $this->yield($month2, $this->formatInt($item[32])),
                        '3' => $this->yield($month3, $this->formatInt($item[32])),
                        '4' => $this->yield($month4, $this->formatInt($item[32])),
                        '5' => $this->yield($month5, $this->formatInt($item[32])),
                        'push' => $this->yield($pushPrice, $this->formatInt($item[32])),
                    ],
                    'cash' => [
                        '1' => $this->yield($month1, $this->formatInt($item[31])),
                        '2' => $this->yield($month2, $this->formatInt($item[31])),
                        '3' => $this->yield($month3, $this->formatInt($item[31])),
                        '4' => $this->yield($month4, $this->formatInt($item[31])),
                        '5' => $this->yield($month5, $this->formatInt($item[31])),
                        'push' => $this->yield($pushPrice, $this->formatInt($item[31])),
                    ],
                ],
            ];
        }

        $this->eps = new Collection(isset($res) ? $res : []);
    }

    /**
     * 殖利率計算
     *
     * @param $price
     * @param $dividend
     *
     * @return false|float
     */
    private function yield($price, $dividend)
    {
        if ($price == 0 || $dividend == 0) {
            return 0;
        }

        return round(($this->formatInt($dividend) / $price) * 100, 1);
    }

    /**
     * @param $value
     *
     * @return int
     */
    private function formatInt($value)
    {
        return $value == "" ? 0 : $value;
    }
}
