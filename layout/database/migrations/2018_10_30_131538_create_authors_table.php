<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('author', function (Blueprint $table) {
            $table->increments('cms_author_id');
            $table->integer('np_author_id');
            $table->string('author_name',250);
            $table->string('author_image',250)->nullabel();
            $table->string('cms_type',100)->nullabel();
            $table->string('authorcol',50)->nullabel();
            $table->string('author_code',50);
            $table->integer('is_updated');
            $table->string('email',250);
            $table->string('author_email',250);
            $table->string('author_phone',50);
            $table->string('author_mobile',50);
            $table->string('author_description',255);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('authors');
    }
}
