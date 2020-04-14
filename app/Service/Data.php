<?php

namespace App\Service;

use App\Exceptions\StockException;
use Carbon\Carbon;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class Data
{
    use InteractsWithIO;

    /**
     * @var string
     */
    private $mainPath;

    /**
     * @var string
     */
    private $pricePath;

    /**
     * @var string
     */
    private $epsPath;

    /**
     * @var string
     */
    private $resultPath;

    /**
     * @var string
     */
    private $keyPath;

    /**
     * @var Collection
     */
    private $key;

    /**
     * @var Collection
     */
    private $main;

    /**
     * @var Collection
     */
    private $price;

    /**
     * @var Collection
     */
    private $eps;

    /**
     * @var Collection
     */
    private $dangchong;

    /**
     * @var Collection
     */
    private $mainKey;

    /**
     * @var Collection
     */
    private $oldFile;

    /**
     * @var Collection
     */
    private $futures;

    /**
     * @var Collection
     */
    private $highEndShipping;

    /**
     * Data constructor.
     *
     * @param string $date
     * @param string $path
     * @param string $key
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function __construct(string $date, string $path, string $key = "")
    {
        $this->mainPath = $path . '\main\main_' . $date . '.xlsx';
        $this->pricePath = $path . '\price\price_' . $date . '.xlsx';
        $this->epsPath = $path . '\eps\eps_' . $date . '.xlsx';
        $this->resultPath = $path . '\result';
        $this->keyPath = $key;

        $this->output = app()->make(
            OutputStyle::class, ['input' => new ArgvInput(), 'output' => new ConsoleOutput()]
        );

        $this->read();
    }

    /**
     * 讀取資料
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function read()
    {
        try {
            $this->info(' ');

            $this->info("key_path: " . $this->keyPath);
            $this->info("main_path: " . $this->mainPath);
            $this->info("price_path: " . $this->pricePath);
            $this->info("eps_path: " . $this->epsPath);
            $this->info("result_path: " . $this->resultPath);

            $this->info("read key list.....");
            $this->key = $this->readKey();

            $this->info("read main list.....");
            $this->main = $this->readMain();

            $this->info("read price list.....");
            $this->price = $this->readPrice();

            $this->info("read eps list.....");
            $this->eps = $this->readEps();

            $this->info("read futures file list.....");
            $this->futures = $this->readFutures();

            $this->highEndShipping = $this->readHighEndShipping();

            $this->mainKey = $this->readMainKey($this->key, $this->main);
            $this->info("map main key list.....");
        } catch (StockException $e) {
            Log::error("code: " . $e->getCode() . ' ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            $this->error($e->getTraceAsString());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            $this->error($e->getTraceAsString());
        }
    }

    /**
     * 關鍵分點
     *
     * @return Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function readKey()
    {
        $spreadsheet = IOFactory::load($this->keyPath);
        $data = $spreadsheet->getSheet(3)->toArray();
        unset($data[0]);

        foreach ($data as $item) {
            $key = collect($item)->slice(2, 5)->whereNotNull();
            if ($key->isNotEmpty()) {
                $res[] = [
                    "code" => $item[0],
                    "name" => $item[1],
                    "key" => $key->toArray(),
                ];
            }
        }

        return new Collection(isset($res) ? $res : []);
    }

    /**
     * 主力進出
     *
     * @return Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function readMain()
    {
        $spreadsheet = IOFactory::load($this->mainPath);
        $data = $spreadsheet->getActiveSheet()->toArray();
        unset($data[0]);

        foreach ($data as $item) {
            $res[] = [
                "code" => $item[0],
                "name" => $item[1],
                "buy" => array_slice($item, 2, 15),
                "sell" => array_slice($item, 17, 15),
            ];
        }

        return new Collection($res);
    }

    /**
     * 0 代號
     * 1 名稱
     * 2 當日開盤價
     * 3 當日收盤價
     * 4 當日漲幅(%)
     * 5 當日最高價
     * 6 當日最低價
     * 7 乖離年線(%)
     * 8 乖離季線(%)
     * 9 乖離月線(%)
     * 10 一年來最高價
     * 11 一年來最低價
     * 12 離高(%)
     * 13 離低(%)
     * 14 mom(%)
     * 15 yoy(%)
     * 16 融資維持率(%)
     * 17 融資使用率(%)
     * 18 股價淨值比
     * 19 1日主力買賣超(%)
     * 20 5日主力買賣超(%)
     * 21 10日主力買賣超(%)
     * 22 20日主力買賣超(%)
     * 23 當日上通道
     * 24 昨日上通道
     * 25 今日上通底
     * 26 昨日上通底
     * 27 今日月線
     * 28 昨日月線
     * 29 昨日月線
     * 30 主力連買N日
     * 31 投信連買N日
     * 32 外資連買N日
     * 33 自營商連買N日
     * 34 融卷強制回補日
     * 35 成交量(股)
     * 36 週轉率(%)
     * 37 股本(千)
     * 38 主力成本
     * 39 外資成本
     * 40 投信成本
     * 41 自營商成本
     * 42 現股當沖交易量
     * 43 資卷當沖交易量
     * 44 20日成交均量
     * 45 當日外資買賣超
     * 46 當日投信買賣超
     * 47 當日自營商買賣超
     *
     * 當日行情
     *
     * @return Collection
     * @throws StockException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function readPrice()
    {
        $spreadsheet = IOFactory::load($this->pricePath);
        $data = $spreadsheet->getActiveSheet()->toArray();
        unset($data[0]);
        $code = '';

        try {
            foreach ($data as $i => $item) {
                $code = $item[0];

                if ($item[28] == 0 || $item[23] == 0) {
                    continue;
                }

                $rank = $this->rank($item[23], $item[25], $item[27], $item[3]);

                $dang_chong = (int)$item[42] + (int)$item[43];
                $volume = floor($item[35] / 1000);
                $volume_20 = (int)$item[44];
                $dangchongbi = 0;
                $volume_20_multiple = 0;

                if ($dang_chong != 0 && $volume != 0) {
                    $dangchongbi = ($dang_chong / $volume) * 100;
                }

                if ($volume != 0 && $volume_20 != 0) {
                    $volume_20_multiple = $volume / $volume_20;
                }

                $res[] = [
                    'code' => $item[0],
                    'name' => $item[1],
                    'increase' => $item[4],
                    'year_stray' => round($item[7]),
                    'season_stray' => round($item[8]),
                    'month_stray' => round($item[9]),
                    'high_stray' => round($item[12]),
                    'low_stray' => round($item[13]),
                    'yoy' => round($item[14]),
                    'mom' => round($item[15]),
                    'financing_maintenance' => round($item[16]),
                    'financing_use' => round($item[17]),
                    'net_worth' => $item[18],
                    "main_1" => round($item[19]),
                    "main_5" => round($item[20]),
                    "main_10" => round($item[21]),
                    "main_20" => round($item[22]),
                    "bb_top_Slope" => $this->countSlopeNum($item[23], $item[24]),
                    "bb_below_Slope" => $this->countSlopeNum($item[25], $item[26]),
                    "month_Slope" => $this->countSlopeNum($item[27], $item[28]),
                    'bandwidth' => round((($item[23] / $item[25]) - 1) * 100),
                    'rank' => $rank->value,
                    'rank_direction' => $rank->direction,
                    'top_diff_lie' => $rank->topDiffLie,
                    'below_diff_lie' => $rank->belowDiffLie,
                    'securities_ratio' => $item[29],
                    'compulsory_replenishment_day' => $item[34] == '' ? '' : $this->createFromFormat($item[34]),
                    'main_buy_n' => $item[30],
                    'trust_buy_n' => $item[31],
                    'foreign_investment_buy_n' => $item[32],
                    'self_employed_buy_n' => $item[33],
                    'volume' => $volume,
                    'turnover' => $item[36],
                    'share_capital' => round($item[37] / 100000),
                    'main_cost' => $item[38],
                    'trust_cost' => $item[39],
                    'foreign_investment_cost' => $item[40],
                    'self_employed_cost' => $item[41],
                    'dangchongbi' => $dangchongbi,
                    'volume_20' => $volume_20,
                    'volume_20_multiple' => round($volume_20_multiple, 1),
                    'foreign_investment_ratio' => $item[45] == '' ? 0 : round(($this->formatInt($item[45]) / $volume) * 100),
                    'credit_ratio' => $item[46] == '' ? 0 : round(($this->formatInt($item[46]) / $volume) * 100),
                    'self_employed_ratio' => $item[47] == '' ? 0 : round(($this->formatInt($item[47]) / $volume) * 100),
                ];
            }
        } catch (\Exception $e) {
            throw new StockException($e, $code);
        }

        return new Collection(isset($res) ? $res : []);
    }

    /**
     * 殖利率
     *
     * @return Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function readEps(): Collection
    {
        return collect();
    }

    /**
     * 當日關鍵分點進出
     *
     * @param Collection $key
     * @param Collection $main
     *
     * @return Collection
     */
    private function readMainKey(Collection $key, Collection $main)
    {
        foreach ($key->toArray() as $value) {
            $buy = [];
            $sell = [];
            $data = $main->where("code", $value["code"])->first();

            foreach ($value["key"] as $k) {
                if (in_array($k, $data["buy"])) {
                    $buy[] = $k;
                }
                if (in_array($k, $data["sell"])) {
                    $sell[] = $k;
                }
            }

            if (count($buy) > 0 || count($sell) > 0) {
                $result[] = [
                    "code" => $value["code"],
                    "name" => $value["name"],
                    "buy" => $buy,
                    "sell" => $sell,
                ];
            }
        }

        return new Collection(isset($result) ? $result : []);
    }

    /**
     * @return Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function readFutures(): Collection
    {
        $spreadsheet = IOFactory::load($this->getResultPath() . '\example.xlsx');
        $data = $spreadsheet->getSheet(3)->toArray();
        unset($data[0]);

        foreach ($data as $item) {
            $res[] = [
                "code" => $item[0],
                'name' => $item[1],
            ];
        }

        return new Collection(isset($res) ? $res : []);
    }

    /**
     * 開檔出貨股名單
     *
     * @return Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    private function readHighEndShipping(): Collection
    {
        $spreadsheet = IOFactory::load($this->getResultPath() . '\HighEndShipping.xlsx');
        $data = $spreadsheet->getSheet(0)->toArray();
        unset($data[0]);

        foreach ($data as $item) {
            $res[] = [
                "code" => $item[0],
                'name' => $item[1],
            ];
        }

        return new Collection(isset($res) ? $res : []);
    }

    /**
     * 位階
     *
     * @param mixed $top
     * @param mixed $below
     * @param mixed $month
     * @param mixed $price
     *
     * @return object
     */
    private function rank($top, $below, $month, $price): object
    {
        $rank = new \stdClass();

        $rank->value = 0;
        $rank->direction = 0;
        $rank->topDiffLie = 0;
        $rank->belowDiffLie = 0;

        if ($month < $price) {
            $rank->direction = 1;
            $diff = $top - $price;
            $width = $top - $month;
        } else {
            $rank->direction = -1;
            $diff = $price - $below;
            $width = $month - $below;
        }

        if ($diff > 0) {
            $rank->value = floor(($width - $diff) / ($width / 10)) * $rank->direction;
            $rank->topDiffLie = round((($top / $price) - 1) * 100, 1);
            $rank->belowDiffLie = round((($below / $price) - 1) * 100, 1);
        }

        return $rank;
    }

    /**
     * 計算斜率
     *
     * @param mixed $value1
     * @param mixed $value2
     *
     * @return float
     */
    private function countSlopeNum($value1, $value2): float
    {
        if ($value1 == 0 || $value2 == 0) {
            return 0;
        }

        $slope = (($value1 / $value2) - 1) * 100;

        $result = round($slope, 1);

        if ($result == 0) {
            $result = $slope > 0 ? 0.1 : -0.1;
        }

        return $result;
    }

    /**
     * @param string $date
     *
     * @return string
     */
    private function createFromFormat(string $date)
    {
        $t = Carbon::createFromFormat('Ymd', $date);
        if ($t->isCurrentYear()) {
            return $t->format("Y-m-d");
        }
        return '';
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

    /**
     * 結果目錄路徑
     *
     * @return string
     */
    public function getResultPath(): string
    {
        return $this->resultPath;
    }

    /**
     * @return Collection
     */
    public function getKey(): Collection
    {
        return $this->key;
    }

    /**
     * @return Collection
     */
    public function getMain(): Collection
    {
        return $this->main;
    }

    /**
     * @return Collection
     */
    public function getPrice(): Collection
    {
        return $this->price;
    }

    /**
     * @return Collection
     */
    public function getMainKey(): Collection
    {
        return $this->mainKey;
    }

    /**
     * @return Collection
     */
    public function getFutures(): Collection
    {
        return $this->futures;
    }

    /**
     * @return Collection
     */
    public function getHighEndShipping(): Collection
    {
        return $this->highEndShipping;
    }

    /**
     * @return Collection
     */
    public function getEps(): Collection
    {
        //        return $this->eps;
        return collect();
    }
}
