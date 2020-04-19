<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateOpenDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('open_dates', function (Blueprint $table) {
            $table->date('date')->primary()->comment('日期');
            $table->string('week')->comment('星期幾');
            $table->boolean('open')->comment('是否開市');
        });

        DB::statement('ALTER TABLE `open_dates` COMMENT = "開市日期"');
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
