<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateIndustryClassificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('industry_classifications', function (Blueprint $table) {
            $table->char('code', 5)->primary()->comment('產業指數代號');
            $table->string('name', 50)->comment('產業名稱');
            $table->string('tw_name', 50)->comment('產業指數名稱');
        });

        DB::statement('ALTER TABLE `industry_classifications` COMMENT = "產業分類"');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('industry_classifications');
    }
}
