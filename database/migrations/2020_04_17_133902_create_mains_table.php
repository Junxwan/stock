<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mains', function (Blueprint $table) {
            $table->string('code', 6)->comment('代碼');
            $table->date('date')->comment('時間');
            $table->string('name')->comment('分點名稱');
            $table->integer('count')->comment('張數');

            $table->index('date');

            $table->foreign('code')->references('code')->on('stocks');
        });

        DB::statement('ALTER TABLE `mains` COMMENT = "主力分點買賣超分點"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mains');
    }
}
