<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWidgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('widget', function (Blueprint $table) {
            $table->increments('cms_widget_id');
            $table->integer('np_widget_id')->nullable();
            $table->integer('page_id')->nullable();
            $table->integer('widget_col')->default(0);
            $table->integer('widget_row')->default(0);
            $table->text('widget_options')->nullable();
            $table->string('cms_type',40)->nullable();
            $table->string('widget_type',255)->nullable();
            $table->integer('parent_widget_id')->nullable();
            $table->text('widget_style')->nullable();
            $table->text('widget_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('widgets');
    }
}
