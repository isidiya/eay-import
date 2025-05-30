<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('section', function (Blueprint $table) {
            $table->increments('cms_section_id');
            $table->integer('np_section_id');
            $table->string('section_name',255);
            $table->string('cms_type',40)->nullable();
            $table->text('section_info')->nullable();
            $table->integer('section_order');
            $table->string('section_color',10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('sections');
    }
}
