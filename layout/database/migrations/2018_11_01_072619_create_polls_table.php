<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poll', function (Blueprint $table) {
            $table->increments('cms_poll_id');
            $table->integer('np_poll_id');
            $table->text('poll_title')->nullable();
            $table->text('poll_answers')->nullable();
            $table->string('cms_type',50)->nullable();
            $table->date('poll_date')->nullable();
            $table->tinyInteger('poll_active')->nullable();
            $table->tinyInteger('poll_multiple')->nullable();
            $table->integer('cms_article_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('polls');
    }
}
