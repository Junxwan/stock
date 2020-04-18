<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->decimal('increase', 4)->comment('漲幅%');
            $table->integer('year_stray')->comment('股價乖離年線%');
            $table->integer('season_stray')->comment('股價乖離季線%');
            $table->integer('month_stray')->comment('股價乖離月線%');
            $table->decimal('last_year_max', self::PRICE_TOTAL)->comment('最近一年(250天)最高價');
            $table->decimal('last_year_min', self::PRICE_TOTAL)->comment('最近一年(250天)最低價');
            $table->date('last_year_date')->comment('最近一年(250天)日期');
            $table->integer('yoy')->comment('yoy%');
            $table->integer('mom')->comment('mom%');
            $table->integer('financing_maintenance')->comment('融資維持率(%)');
            $table->integer('financing_use')->comment('融資使用率');
            $table->decimal('net_worth', 4)->comment('股價淨值比');
            $table->tinyInteger('main_1')->comment('1日主力買賣超(%)');
            $table->tinyInteger('main_5')->comment('5日主力買賣超(%)');
            $table->tinyInteger('main_10')->comment('10日主力買賣超(%)');
            $table->tinyInteger('main_20')->comment('20日主力買賣超(%)');
            $table->integer('foreign_investment_buy')->comment('外資買賣超(張)');
            $table->integer('trust_buy')->comment('投信買賣超(張)');
            $table->integer('self_employed_buy')->comment('自營商買賣超(張)');
            $table->decimal('bb_top', self::PRICE_TOTAL)->comment('上通道');
            $table->decimal('bb_below', self::PRICE_TOTAL)->comment('下通道');
            $table->decimal('month_ma', self::PRICE_TOTAL)->comment('月線');
            $table->integer('securities_ratio')->comment('券資比');
            $table->date('compulsory_replenishment_day')->nullable()->comment('融券回補日');
            $table->tinyInteger('main_buy_n')->comment('分點連買N日');
            $table->tinyInteger('trust_buy_n')->comment('投信連買N日');
            $table->tinyInteger('foreign_investment_buy_n')->comment('外資連買N日');
            $table->tinyInteger('self_employed_buy_n')->comment('自營商連買N日');
            $table->integer('volume')->comment('成交量(張)');
            $table->integer('volume_20')->comment('20日成交均量(張)');
            $table->decimal('turnover', 4)->comment('週轉率(%)');
            $table->decimal('main_cost', self::PRICE_TOTAL)->comment('主力成本');
            $table->decimal('trust_cost', self::PRICE_TOTAL)->comment('投信成本');
            $table->decimal('foreign_investment_cost', self::PRICE_TOTAL)->comment('外資成本');
            $table->decimal('self_employed_cost', self::PRICE_TOTAL)->comment('自營商成本');
            $table->integer('stock_trading_volume')->comment('現股當沖交易量');
            $table->integer('credit_trading_volume')->comment('資卷當沖交易量');

            $table->index('date');
            $table->foreign('code')->references('code')->on('stocks');
        });
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
