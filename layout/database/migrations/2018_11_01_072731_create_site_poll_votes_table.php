<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSitePollVotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_poll_vote', function (Blueprint $table) {
            $table->increments('site_poll_vote_id');
            $table->string('site_poll_vote_ip',145);
            $table->string('site_poll_id');
            $table->integer('site_poll_answer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('site_poll_votes');
    }
}
