<?php

namespace App\Service\Import;

use App\Exceptions\StockException;
use App\Repository\PriceRepository;
use App\Service\Xlsx\Xlsx;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Price extends Import
{
    /**
     * @var PriceRepository
     */
    private $repo;

    /**
     * EPS constructor.
     *
     * @param PriceRepository $repo
     * @param Xlsx $xlsx
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(PriceRepository $repo, Xlsx $xlsx)
    {
        $this->repo = $repo;
        parent::__construct($xlsx);
    }

    /**
     * 0 代碼
     * 1 名稱
     * 2 開盤價
     * 3 收盤價
     * 4 漲幅%
     * 5 最高價
     * 6 最低價
     * 7 股價乖離年線(%)
     * 8 股價乖離季線(%)
     * 9 股價乖離月線(%)
     * 10 最近一年(250天)最高價
     * 11 最近一年(250天)最低價
     * 12 yoy
     * 13 mom
     * 14 融資維持率(%)
     * 15 融資資使用率(%)
     * 16 股價淨值比
     * 17 1日主力買賣超(%)
     * 18 5日主力買賣超(%)
     * 19 10日主力買賣超(%)
     * 20 20日主力買賣超(%)
     * 21 上通道
     * 22 下通道
     * 23 月線
     * 24 券資比
     * 25 分點連買N日
     * 26 投信連買N日
     * 27 外資連買N日
     * 28 自營商連買N日
     * 29 融券回補日
     * 30 成交量(股)
     * 31 週轉率(%)
     * 32 主力成本
     * 33 外資持股成本
     * 34 投信持股成本
     * 35 自營商持股成本
     * 36 現股當沖成交量
     * 37 資券相抵成交量
     * 38 20日成交均量
     * 39 外資買賣超
     * 40 投信買賣超
     * 41 自營商買賣超
     *
     *
     * @param Collection $data
     *
     * @return bool
     */
    protected function insert(Collection $data): bool
    {
        $code = 0;
        $header = $data[0];
        unset($data[0]);

        $codes = $this->getCodes($data);

        if (count($codes['repeat']) > 0) {
            foreach ($codes['repeat'] as $code) {
                $d = $data->where('0', $code);
                if ($d->count() != 2) {
                    throw new \Exception('repeat count is not 2');
                }

                $d = $d->where('29', '');

                if ($d->isEmpty()) {
                    throw new \Exception($code . ' compulsory replenishment day is not empty');
                }

                unset($data[$d->keys()->get(0)]);
            }

            if ($this->checkDiff($codes)) {
                return true;
            }

        } elseif ($this->checkRepeat($codes) || $this->checkDiff($codes)) {
            return true;
        }

        $lastYearDate = Carbon::createFromFormat('Ymd', explode('~', $header[10])[0])->format('Y-m-d');
        $existCodes = $this->repo->date($this->date)->pluck('code')->all();
        $saveTotal = 0;
        $noOpen = 0;

        try {
            $total = $data->count();
            foreach ($data->toArray() as $value) {
                $code = $value[0];

                // 資料已存在
                if (in_array($code, $existCodes)) {
                    continue;
                }

                // 沒開盤價
                if ($value[2] == 0) {
                    $noOpen++;
                    continue;
                }

                $volume = floor($value[30] / 1000);

                $result = $this->repo->insert([
                    'code' => $code,
                    'date' => $this->date,
                    'open' => $value[2],
                    'close' => $value[3],
                    'increase' => $value[4],
                    'max' => $value[5],
                    'min' => $value[6],
                    'year_stray' => round($value[7]),
                    'season_stray' => round($value[8]),
                    'month_stray' => round($value[9]),
                    'last_year_max' => $value[10],
                    'last_year_min' => $value[11],
                    'last_year_date' => $lastYearDate,
                    'yoy' => round($value[12]),
                    'mom' => round($value[13]),
                    'financing_maintenance' => $this->round($value[14]),
                    'financing_use' => $this->round($value[15]),
                    'net_worth' => $this->round($value[16], 2),
                    'bb_top' => $this->formatInt($value[21]),
                    'bb_below' => $this->formatInt($value[22]),
                    'month_ma' => $this->formatInt($value[23]),
                    'main_1' => round($value[17]),
                    'main_5' => round($value[18]),
                    'main_10' => round($value[19]),
                    'main_20' => round($value[20]),
                    'foreign_investment_buy' => $value[39],
                    'trust_buy' => $value[40],
                    'self_employed_buy' => $value[41],
                    'securities_ratio' => $this->round($value[24]),
                    'compulsory_replenishment_day' => $this->formatDate($value[29]),
                    'main_buy_n' => $value[25],
                    'trust_buy_n' => $value[26],
                    'foreign_investment_buy_n' => $value[27],
                    'self_employed_buy_n' => $value[28],
                    'volume' => $volume,
                    'volume_20' => $value[38],
                    'turnover' => $value[31],
                    'main_cost' => $this->formatInt($value[32]),
                    'trust_cost' => $this->formatInt($value[33]),
                    'foreign_investment_cost' => $this->formatInt($value[34]),
                    'self_employed_cost' => $this->formatInt($value[35]),
                    'stock_trading_volume' => $this->formatInt($value[36]),
                    'credit_trading_volume' => $this->formatInt($value[37]),
                ]);

                if ($result) {
                    $this->info('code: ' . $code);
                    $saveTotal++;
                } else {
                    $this->error('error code: ' . $code);
                    return false;
                }
            }
        } catch (\Exception $e) {
            throw new StockException($e, $code);
        }

        $this->info('total: ' . $total . ' save: ' . $saveTotal . ' no open: ' . $noOpen);

        return true;
    }
}
