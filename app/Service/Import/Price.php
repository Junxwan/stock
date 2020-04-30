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
     * 4 漲幅(%)
     * 5 振福(%)
     * 6 最高價
     * 7 最低價
     * 8 最近一年(250天)最高價
     * 9 最近一年(250天)最低價
     * 10 5日均線
     * 11 10日均線
     * 12 20日均線
     * 13 60日均線
     * 14 240日均線
     * 15 股價乖離5日均線(%)
     * 16 股價乖離10日均線(%)
     * 17 股價乖離月線(%)
     * 18 股價乖離季線(%)
     * 19 股價乖離年線(%)
     * 20 1日主力買賣超(%)
     * 21 5日主力買賣超(%)
     * 22 10日主力買賣超(%)
     * 23 20日主力買賣超(%)
     * 24 上通道
     * 25 下通道
     * 26 外資買賣超
     * 27 外資持股張數
     * 28 外資持股比率
     * 29 投信買賣超
     * 30 投信持股張數
     * 31 投信持股比率
     * 32 自營商買賣超
     * 33 自營商買賣超(自行買賣)
     * 34 自營商買賣超(避險)
     * 35 自營商持股張數
     * 36 自營商持股比率
     * 37 主力連買N日
     * 38 投信連買N日
     * 39 外資連買N日
     * 40 自營商連買N日
     * 41 成交量(張數)
     * 42 20日成交均量(張數)
     * 43 現股當沖成交量
     * 44 資券相抵成交量
     * 45 yoy
     * 46 mom
     * 47 融資維持率(%)
     * 48 融資資使用率(%)
     * 49 券資比
     * 50 週轉率(%)
     * 51 股價淨值比
     * 52 融券回補日
     * 53 主力成本
     * 54 外資持股成本
     * 55 投信持股成本
     * 56 自營商持股成本
     * 57 今日借卷賣出
     * 58 借卷賣出餘額
     * 59 證交所借卷餘額
     * 60 卷商借卷餘額
     * 61 買賣分點家數差
     * 62 有買賣分點總家數
     * 63 當沖買進成交金額(千)
     * 64 當沖賣出成交金額(千)
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

                $d = $d->where('52', '');

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

        $lastYearDate = Carbon::createFromFormat('Ymd', explode('~', $header[8])[0])->format('Y-m-d');
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

                $result = $this->repo->insert([
                    'code' => $code,
                    'date' => $this->date,
                    'open' => $value[2],
                    'close' => $value[3],
                    'max' => $value[6],
                    'min' => $value[7],
                    'increase' => $value[4],
                    'amplitude' => $value[5],
                    'last_year_max' => $value[8],
                    'last_year_min' => $value[9],
                    'last_year_date' => $lastYearDate,
                    '5ma' => $this->format($value[10]),
                    '10ma' => $this->format($value[11]),
                    '20ma' => $this->format($value[12]),
                    '60ma' => $this->format($value[13]),
                    '240ma' => $this->format($value[14]),
                    '5_stray' => $this->round($value[15], 2),
                    '10_stray' => $this->round($value[16], 2),
                    'month_stray' => $this->round($value[17], 2),
                    'season_stray' => $this->round($value[18], 2),
                    'year_stray' => $this->round($value[19], 2),
                    'main_1' => round($value[20]),
                    'main_5' => round($value[21]),
                    'main_10' => round($value[22]),
                    'main_20' => round($value[23]),
                    'bb_top' => $this->format($value[24]),
                    'bb_below' => $this->format($value[25]),
                    'foreign_investment_buy' => $value[26],
                    'foreign_investment_total' => $value[27],
                    'foreign_investment_ratio' => $value[28],
                    'trust_buy' => $value[29],
                    'trust_total' => $value[30],
                    'trust_ratio' => $value[31],
                    'self_employed_buy' => $value[32],
                    'self_employed_buy_by_self' => $value[33],
                    'self_employed_buy_by_hedging' => $value[34],
                    'self_employed_total' => $value[35],
                    'self_employed_ratio' => $value[36],
                    'main_buy_n' => $value[37],
                    'trust_buy_n' => $value[38],
                    'foreign_investment_buy_n' => $value[39],
                    'self_employed_buy_n' => $value[40],
                    'volume' => $value[41],
                    'volume20' => $value[42],
                    'stock_trading_volume' => $this->format($value[43]),
                    'credit_trading_volume' => $this->format($value[44]),
                    'yoy' => round($value[45]),
                    'mom' => round($value[46]),
                    'financing_maintenance' => $this->round($value[47]),
                    'financing_use' => $this->format($value[48]),
                    'securities_ratio' => $this->round($value[49]),
                    'turnover' => $value[50],
                    'net_worth' => $this->round($value[51], 2),
                    'compulsory_replenishment_day' => $this->formatDate($value[52]),
                    'main_cost' => $this->format($value[53]),
                    'trust_cost' => $this->format($value[54]),
                    'foreign_investment_cost' => $this->format($value[55]),
                    'self_employed_cost' => $this->format($value[56]),
                    'sell_by_coupon' => $this->format($value[57]),
                    'borrowing_the_balance' => $this->format($value[58]),
                    'debit_balance' => $this->format($value[59]) + $this->format($value[60]),
                    'stock_exchange_borrowing_balance' => $this->format($value[59]),
                    'volume_merchant_balance' => $this->format($value[60]),
                    'buy_sell_point_diff' => $this->format($value[61]),
                    'buy_sell_main_count' => $this->format($value[62]),
                    'buy_trading_amount' => $this->round($value[63]),
                    'sell_trading_amount' => $this->round($value[64]),
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
