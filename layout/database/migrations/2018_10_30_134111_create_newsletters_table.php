<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewslettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newsletter', function (Blueprint $table) {
            $table->increments('cms_newsletter_id');
            $table->string('newsletter_email',150);
            $table->string('newsletter_fname',150)->nullable();
            $table->string('newsletter_lname',150)->nullable();
            $table->string('newsletter_phone',150)->nullable();
            $table->string('newsletter_gender',150)->nullable();
            $table->string('newsletter_age_group',150)->nullable();
            $table->integer('cms_country_id')->nullable();
            $table->string('newsletter_address1',255)->nullable();
            $table->string('newsletter_address2',255)->nullable();
            $table->tinyInteger('newsletter_subscribed')->nullable();
            $table->string('newsletter_random_number',145)->nullable();
            $table->integer('section_id');
            $table->tinyInteger('is_newsletter_sent');
            $table->tinyInteger('hard_copy');
            $table->timeStamp('date_created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('newsletters');
    }
}
