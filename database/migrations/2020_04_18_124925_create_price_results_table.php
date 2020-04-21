<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePriceResultsTable extends Migration
{
    const PRICE_TOTAL = 6;
    const LIE_TOTAL = 3;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_results', function (Blueprint $table) {
            $table->string('code', 6)->comment('代碼');
            $table->date('date')->comment('時間');
            $table->tinyInteger('rank')->comment('位階');
            $table->smallInteger('bandwidth')->unsigned()->comment('帶寬');
            $table->smallInteger('pe')->comment('本益比');
            $table->decimal('yield', self::LIE_TOTAL, 1)->comment('殖利率');
            $table->decimal('cash_yield', self::LIE_TOTAL, 1)->comment('現金殖利率');
            $table->decimal('month_Slope', self::LIE_TOTAL, 1)->comment('月線斜率');
            $table->decimal('bb_top_Slope', self::LIE_TOTAL, 1)->comment('上通道斜率');
            $table->decimal('bb_below_Slope', self::LIE_TOTAL, 1)->comment('下通道斜率');
            $table->decimal('top_diff', self::PRICE_TOTAL)->comment('離上通%');
            $table->decimal('below_diff', self::PRICE_TOTAL)->comment('離下通%');
            $table->smallInteger('high_stray')->comment('離高點打幾% (收盤價/最近一年(250天)最高價)*100');
            $table->smallInteger('low_stray')->comment('離低點漲了多少% ((收盤價/最近一年(250天)最低價)-1)*100');
            $table->decimal('volume_20_multiple', self::LIE_TOTAL, 1)->comment('20日成交均量幾倍');

            $table->index('date');
            $table->foreign('code')->references('code')->on('stocks');
        });

        DB::statement('ALTER TABLE `price_results` COMMENT = "個股分析"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_results');
    }
}
