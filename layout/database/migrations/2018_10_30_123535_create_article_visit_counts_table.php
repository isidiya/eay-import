<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleVisitCountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_visit_count', function (Blueprint $table) {
            $table->increments('avc_id');
            $table->integer('cms_article_id');
            $table->timestamp('visit_time');
            $table->integer('visit_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('article_visit_counts');
    }
}
