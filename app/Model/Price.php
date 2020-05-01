<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $casts = [
        'date' => 'date',

        'open' => 'double',
        'close' => 'double',
        'max' => 'double',
        'min' => 'double',

        'increase' => 'double',
        'amplitude' => 'double',

        'last_year_max' => 'double',
        'last_year_min' => 'double',

        '5ma' => 'double',
        '10ma' => 'double',
        '20ma' => 'double',
        '60ma' => 'double',
        '240ma' => 'double',

        '5stray' => 'double',
        '10stray' => 'double',
        'month_stray' => 'double',
        'season_stray' => 'double',
        'year_stray' => 'double',

        'main1' => 'double',
        'main5' => 'double',
        'main10' => 'double',
        'main20' => 'double',

        'bb_top' => 'double',
        'bb_below' => 'double',

        'foreign_investment_ratio' => 'double',
        'trust_ratio' => 'double',
        'self_employed_ratio' => 'double',

        'financing_maintenance' => 'double',
        'financing_use' => 'double',
        'securities_ratio' => 'double',
        'turnover' => 'double',

        'net_worth' => 'double',

        'main_cost' => 'double',
        'foreign_investment_cost' => 'double',
        'trust_cost' => 'double',
        'self_employed_cost' => 'double',

        'buy_trading_amount' => 'double',
        'sell_trading_amount' => 'double',
    ];

    public $timestamps = false;
}
