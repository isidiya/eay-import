<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_section', function (Blueprint $table) {
            $table->increments('cms_sub_section_id');
            $table->string('sub_section_name',255);
            $table->text('sub_section_info')->nullable();
            $table->integer('section_id');
            $table->integer('sub_section_order');
            $table->integer('np_sub_section_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sub_sections');
    }
}
