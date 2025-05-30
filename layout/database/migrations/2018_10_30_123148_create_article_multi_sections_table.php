<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleMultiSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article_multi_section', function (Blueprint $table) {
            $table->increments('ams_id');
            $table->integer('ams_article_id')->index();
            $table->integer('ams_country_id')->index();
            $table->integer('ams_section_id')->index();
            $table->integer('ams_subsection_id')->index();
            $table->datetime('ams_article_date')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article_multi_sections');
    }
}
