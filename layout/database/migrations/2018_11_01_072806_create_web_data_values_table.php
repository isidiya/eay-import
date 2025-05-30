<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebDataValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_data_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cms_article_id')->index();
            $table->string('data_key',200)->index();
            $table->string('data_value',200)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('web_data_values');
    }
}
