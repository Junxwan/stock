<?php

namespace App\Service\Xlsx\Year;

use App\Service\Xlsx\Xlsx;

class All extends Xlsx
{
    /**
     * @var string[]
     */
    private $dir = [
        'open', // 開盤價
        'close', // 收盤價
        'max', // 最高價
        'min', // 最低價

        'increase', // 漲幅(%)
        'amplitude', // 振幅(%)

        '5ma', // 5日均線
        '10ma', // 10日均線
        '20ma', // 20日均線
        '60ma', // 60日均線
        '240ma', // 240日均線

        'month_stray', // 股價乖離月線%
        'season_stray', // 股價乖離季線%
        'year_stray', // 股價乖離年線%

        'main1', // 1日主力買賣超(%)
        'main5', // 5日主力買賣超(%)
        'main10', // 10日主力買賣超(%)
        'main20', // 20日主力買賣超(%)

        'bb_top', // 上通道
        'bb_below', // 下通道

        'foreign_investment_buy', // 外資買賣超(張)
        'foreign_investment_total', // 外資持股張數
        'foreign_investment_ratio', // 外資持股比率(%)

        'trust_buy', // 投信買賣超(張)
        'trust_total', // 投信持股張數
        'trust_ratio', // 投信持股比率(%)

        'self_employed_buy', // 自營商買賣超(張)
        'self_employed_buy_by_self', // 自營商買賣超(張)(自行買賣)
        'self_employed_buy_by_hedging', // 自營商買賣超(張)(避險)
        'self_employed_total', // 自營商持股張數
        'self_employed_ratio', // 自營商持股比率(%)

        'main_buy_n', // 主力分點連買N日
        'trust_buy_n', // 投信連買N日
        'foreign_investment_buy_n', // 外資連買N日
        'self_employed_buy_n', // 自營商連買N日

        'volume', // 成交量(張)
        'stock_trading_volume', // 現股當沖交易量
        'credit_trading_volume', // 資卷當沖交易量
        'turnover', // 週轉率(%)
        'buy_trading_amount', // 當沖買進成交金額(千)
        'sell_trading_amount', // 當沖賣出成交金額(千)

        'financing_maintenance', // 融資維持率(%)
        'financing_use', // 融資使用率
        'securities_ratio', // 券資比

        'net_worth', // 股價淨值比

        'main_cost', // 主力成本
        'foreign_investment_cost', // 外資成本
        'trust_cost', // 投信成本
        'self_employed_cost', // 自營商成本

        'sell_by_coupon', // 今日借卷賣出
        'borrowing_the_balance', // 借卷賣出餘額(累積賣出)
        'stock_exchange_borrowing_balance', // 證交所借卷餘額(累積借出)
        'volume_merchant_balance', // 卷商借卷餘額(累積借出)

        'buy_sell_point_diff', // 買賣分點家數差
        'buy_sell_main_count', // 有買賣分點總家數
    ];

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getData()
    {
        $lastYear = $this->year - 1;

        return collect([
            $this->year => $this->getAllData($this->dir, $this->name()),
            $lastYear => $this->getAllData(['max', 'min', 'volume'], $this->name($lastYear)),
        ]);
    }
}
