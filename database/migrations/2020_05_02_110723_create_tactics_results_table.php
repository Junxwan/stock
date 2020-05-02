<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTacticsResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tactics_results', function (Blueprint $table) {
            $table->string('code', 6)->comment('代碼');
            $table->date('date')->comment('日期');
            $table->string('type')->comment('策略');

            $table->index('date');
            $table->foreign('code')->references('code')->on('stocks');
        });

        DB::statement('ALTER TABLE `tactics_results` COMMENT = "策略結果"');
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
