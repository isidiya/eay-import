<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('image', function (Blueprint $table) {
            $table->increments('cms_image_id');
            $table->integer('np_image_id');
            $table->text('image_caption')->nullable();
            $table->integer('np_related_article_id');
            $table->string('cms_type',30)->nullable();
            $table->text('image_description',30)->nullable();
            $table->string('image_path',255);
            $table->integer('media_type')->nullable(1);
            $table->tinyInteger('is_old_image')->default(0);
            $table->string('small_image',500)->default('');
            $table->tinyInteger('is_updated')->default(0);
            $table->text('image_cropping');
            $table->integer('media_order');
            $table->tinyInteger('is_copied')->default(0);
            $table->tinyInteger('image_is_deleted')->default(0);
            $table->text('image_alt_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('images');
    }
}
