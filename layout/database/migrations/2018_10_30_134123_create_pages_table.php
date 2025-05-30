<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page', function (Blueprint $table) {
            $table->increments('cms_page_id');
            $table->integer('np_page_id');
            $table->string('cms_type',50)->nullable();
            $table->string('page_title',255)->nullable();
            $table->string('page_link',255);
            $table->text('header_script')->nullable();
            $table->text('seo_meta_keywords')->nullable();
            $table->text('seo_meta_description')->nullable();
            $table->text('seo_meta_title')->nullable();
            $table->string('device_type',100);
            $table->integer('is_home_page')->nullable(0);
            $table->tinyInteger('is_subscribe_page');
            $table->tinyInteger('send_newsletter');
            $table->integer('page_section_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('pages');
    }
}
