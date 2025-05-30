<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSitePollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_poll', function (Blueprint $table) {
            $table->increments('site_poll_id');
            $table->text('site_poll_question')->nullable();
            $table->date('site_poll_date')->nullable();
            $table->tinyInteger('site_poll_is_active')->nullable();
            $table->integer('np_poll_id');
            $table->string('cms_type',40)->nullable();
            $table->tinyInteger('poll_multiple')->default(0);
            $table->integer('np_article_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('site_polls');
    }
}
