<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePricesTable extends Migration
{
    const PRICE_TOTAL = 6;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prices', function (Blueprint $table) {
            $table->string('code', 6)->comment('代碼');
            $table->date('date')->comment('時間');
            $table->decimal('open', self::PRICE_TOTAL)->comment('開盤價');
            $table->decimal('close', self::PRICE_TOTAL)->comment('收盤價');
            $table->decimal('max', self::PRICE_TOTAL)->comment('最高價');
            $table->decimal('min', self::PRICE_TOTAL)->comment('最低價');
            $table->decimal('increase', 4)->comment('漲幅(%)');
            $table->decimal('amplitude', 4)->comment('振幅(%)');
            $table->decimal('last_year_max', self::PRICE_TOTAL)->comment('最近一年(250天)最高價');
            $table->decimal('last_year_min', self::PRICE_TOTAL)->comment('最近一年(250天)最低價');
            $table->date('last_year_date')->nullable()->comment('最近一年(250天)日期');
            $table->decimal('5ma', self::PRICE_TOTAL)->comment('5日均線');
            $table->decimal('10ma', self::PRICE_TOTAL)->comment('10日均線');
            $table->decimal('20ma', self::PRICE_TOTAL)->comment('20日均線');
            $table->decimal('60ma', self::PRICE_TOTAL)->comment('60日均線');
            $table->decimal('240ma', self::PRICE_TOTAL)->comment('240日均線');
            $table->decimal('5_stray', 5)->comment('股價乖離5日均線%');
            $table->decimal('10_stray', 5)->comment('股價乖離10日均線%');
            $table->decimal('month_stray', 5)->comment('股價乖離月線%');
            $table->decimal('season_stray', 5)->comment('股價乖離季線%');
            $table->decimal('year_stray', 5)->comment('股價乖離年線%');
            $table->tinyInteger('main_1')->comment('1日主力買賣超(%)');
            $table->tinyInteger('main_5')->comment('5日主力買賣超(%)');
            $table->tinyInteger('main_10')->comment('10日主力買賣超(%)');
            $table->tinyInteger('main_20')->comment('20日主力買賣超(%)');
            $table->decimal('bb_top', self::PRICE_TOTAL)->comment('上通道');
            $table->decimal('bb_below', self::PRICE_TOTAL)->comment('下通道');
            $table->integer('foreign_investment_buy')->comment('外資買賣超(張)');
            $table->integer('foreign_investment_total')->comment('外資持股張數');
            $table->decimal('foreign_investment_ratio', 5)->comment('外資持股比率(%)');
            $table->integer('trust_buy')->comment('投信買賣超(張)');
            $table->integer('trust_total')->comment('投信持股張數');
            $table->decimal('trust_ratio', 5)->comment('投信持股比率(%)');
            $table->integer('self_employed_buy')->comment('自營商買賣超(張)');
            $table->integer('self_employed_buy_by_self')->comment('自營商買賣超(張)(自行買賣)');
            $table->integer('self_employed_buy_by_hedging')->comment('自營商買賣超(張)(避險)');
            $table->integer('self_employed_total')->comment('自營商持股張數');
            $table->decimal('self_employed_ratio', 5)->comment('自營商持股比率(%)');
            $table->tinyInteger('main_buy_n')->comment('主力分點連買N日');
            $table->tinyInteger('trust_buy_n')->comment('投信連買N日');
            $table->tinyInteger('foreign_investment_buy_n')->comment('外資連買N日');
            $table->tinyInteger('self_employed_buy_n')->comment('自營商連買N日');
            $table->integer('volume')->comment('成交量(張)');
            $table->integer('volume20')->comment('20日成交均量(張)');
            $table->integer('stock_trading_volume')->comment('現股當沖交易量');
            $table->integer('credit_trading_volume')->comment('資卷當沖交易量');
            $table->integer('yoy')->comment('yoy%');
            $table->integer('mom')->comment('mom%');
            $table->integer('financing_maintenance')->comment('融資維持率(%)');
            $table->decimal('financing_use', 6)->comment('融資使用率');
            $table->integer('securities_ratio')->comment('券資比');
            $table->decimal('turnover', 4)->comment('週轉率(%)');
            $table->decimal('net_worth', 4)->comment('股價淨值比');
            $table->date('compulsory_replenishment_day')->nullable()->comment('融券回補日');
            $table->decimal('main_cost', self::PRICE_TOTAL)->comment('主力成本');
            $table->decimal('foreign_investment_cost', self::PRICE_TOTAL)->comment('外資成本');
            $table->decimal('trust_cost', self::PRICE_TOTAL)->comment('投信成本');
            $table->decimal('self_employed_cost', self::PRICE_TOTAL)->comment('自營商成本');
            $table->integer('sell_by_coupon')->comment('今日借卷賣出');
            $table->integer('borrowing_the_balance')->comment('借卷賣出餘額');
            $table->integer('debit_balance')->comment('借卷餘額 = 證交所借卷餘額 + 卷商借卷餘額');
            $table->integer('stock_exchange_borrowing_balance')->comment('證交所借卷餘額');
            $table->integer('volume_merchant_balance')->comment('卷商借卷餘額');
            $table->smallInteger('buy_sell_point_diff')->comment('買賣分點家數差');
            $table->smallInteger('buy_sell_main_count')->comment('有買賣分點總家數');
            $table->integer('buy_trading_amount')->comment('當沖買進成交金額(千)');
            $table->integer('sell_trading_amount')->comment('當沖賣出成交金額(千)');

            $table->index('date');
            $table->foreign('code')->references('code')->on('stocks');
        });

        DB::statement('ALTER TABLE `prices` COMMENT = "個股每天行情"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prices');
    }
}
