<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('points', function (Blueprint $table) {
            $table->char('code', 4)->comment('代碼');
            $table->string('name', 20)->comment('名稱');
            $table->tinyInteger('type')->comment('0:一般,1:外資,2:官股,3:經記部');

            $table->index('code');
        });

        DB::statement('ALTER TABLE `points` COMMENT = "分點"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('points');
    }
}
