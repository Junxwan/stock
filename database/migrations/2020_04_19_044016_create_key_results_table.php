<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateKeyResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('key_results', function (Blueprint $table) {
            $table->string('code', 6)->comment('代碼');
            $table->date('date')->comment('時間');
            $table->string('point_code', 5)->comment('分點代碼');
            $table->tinyInteger('type')->comment('0:賣,1:買');
            $table->tinyInteger('order')->comment('幾名');

            $table->index('date');
            $table->foreign('code')->references('code')->on('stocks');
            $table->foreign('point_code')->references('code')->on('points');
        });

        DB::statement('ALTER TABLE `key_results` COMMENT = "關鍵分點進出"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('key_results');
    }
}
