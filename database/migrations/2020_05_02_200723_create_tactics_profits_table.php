<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTacticsProfitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tactics_profits', function (Blueprint $table) {
            $table->string('code', 6)->comment('代碼');
            $table->date('date')->comment('符合策略日期');
            $table->date('start_date')->comment('進場日期');
            $table->decimal('start_price')->comment('進場價格');
            $table->date('end_date')->comment('出場日期');
            $table->decimal('end_price')->comment('出場價格');
            $table->decimal('price')->comment('價差');
            $table->decimal('increase', 6)->comment('漲幅(%)');
            $table->tinyInteger('action')->comment('先買後賣:1,先賣後買:0');
            $table->string('tactics')->comment('策略選股');
            $table->string('type')->comment('策略盈虧');
            $table->integer('amount')->comment('盈虧金額');
            $table->decimal('rate_of_return')->comment('報酬率');
            $table->integer('buy_fee')->comment('買進手續費(卷商)');
            $table->integer('sell_fee')->comment('賣出手續費(卷商)');
            $table->integer('tax')->comment('交易稅(證交稅)');
            $table->integer('financing_interest')->comment('融資利息');
            $table->integer('borrowing_interest')->comment('借卷費');

            $table->index('type');
            $table->index('date');
            $table->foreign('code')->references('code')->on('stocks');
        });

        DB::statement('ALTER TABLE `tactics_profits` COMMENT = "策略盈虧"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('open_dates');
    }
}
