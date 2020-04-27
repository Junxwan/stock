<?php

namespace App\Service\Xlsx\Year;

use App\Service\Xlsx\Xlsx;

class All extends Xlsx
{
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
    ];

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getData()
    {
        return $this->getAllData($this->dir);
    }
}
