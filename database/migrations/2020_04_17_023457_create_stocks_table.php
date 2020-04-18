<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->string('code', 6)->primary()->comment('代碼');
            $table->string('name', 50)->comment('名稱');
            $table->integer('capital')->comment('股本(千)');
            $table->char('industry_code', 5)->nullable()->comment('產業分類');
            $table->char('classification', 50)->comment('細產業分類');
            $table->tinyInteger('issued')->comment('上市:1 上櫃:2');
            $table->date('twse_date')->nullable()->comment('上市日期');
            $table->date('otc_date')->nullable()->comment('上櫃日期');
            $table->date('creation_date')->nullable()->comment('成立日期');

            $table->foreign('industry_code')->references('code')->on('industry_classifications');
        });

        DB::statement('ALTER TABLE `stocks` COMMENT = "股票"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stocks');
    }
}
