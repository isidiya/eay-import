<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModifiedAticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('modified_aticles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cms_article_id')->default(0);
            $table->integer('np_article_id');
            $table->integer('flag');
            $table->text('from');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modified_aticles');
    }
}
