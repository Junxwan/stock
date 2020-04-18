<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEpsTable extends Migration
{
    const DECIMAL_TOTAL = 5;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eps', function (Blueprint $table) {
            $table->string('code', 6)->comment('代碼');
            $table->year('year')->comment('年分');
            $table->decimal('q1', self::DECIMAL_TOTAL)->comment('q1 eps');
            $table->decimal('q2', self::DECIMAL_TOTAL)->comment('q2 eps');
            $table->decimal('q3', self::DECIMAL_TOTAL)->comment('q3 eps');
            $table->decimal('q4', self::DECIMAL_TOTAL)->comment('q4 eps');
            $table->decimal('q1_q3', self::DECIMAL_TOTAL)->comment('q1~q3 eps');
            $table->decimal('eps', self::DECIMAL_TOTAL)->comment('今年 eps');
            $table->decimal('dividend_stock_surplus', self::DECIMAL_TOTAL)->comment('股利政策-盈餘配股');
            $table->decimal('dividend_stock_provident', self::DECIMAL_TOTAL)->comment('股利政策-公積配股');
            $table->decimal('dividend_stock_total', self::DECIMAL_TOTAL)->comment('股利政策-股票股利合計 = 盈餘配股 + 公積配股');
            $table->decimal('dividend_cash_surplus', self::DECIMAL_TOTAL)->comment('股利政策-盈餘配息');
            $table->decimal('dividend_cash_provident', self::DECIMAL_TOTAL)->comment('股利政策-公積配息');
            $table->decimal('dividend_cash_cash', self::DECIMAL_TOTAL)->comment('股利政策-現金股利 = 盈餘配息 + 公積配息');
            $table->decimal('dividend_cash_total', self::DECIMAL_TOTAL)->comment('股利政策-年度現金股利 = 盈餘配息 + 公積配息');
            $table->decimal('dividend_total', self::DECIMAL_TOTAL)->comment('股利政策-年度股利合計 = 股票股利合計 + 年度現金股利');
            $table->decimal('distribution_rate_stock')->comment('發放率-股票');
            $table->decimal('distribution_rate_cash')->comment('發放率-現金');
            $table->decimal('distribution_rate_total')->comment('總發放率= 股票 + 現金');
            $table->tinyInteger('n_rate')->default(0)->comment('連續發放次數(年度)');
            $table->date('ex_dividend_day')->nullable()->comment('除權/息日');
            $table->date('resolution_meeting')->nullable()->comment('董事會決議派發日');

            $table->index('year');

            $table->foreign('code')->references('code')->on('stocks');
        });

        DB::statement('ALTER TABLE `eps` COMMENT = "eps"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eps');
    }
}
