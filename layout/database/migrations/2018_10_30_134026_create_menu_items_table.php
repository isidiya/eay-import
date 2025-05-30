<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_item', function (Blueprint $table) {
            $table->increments('cms_menu_items_id');
            $table->integer('np_menu_items_id')->unique()->index();
            $table->string('cms_type',50)->nullable();
            $table->string('menu_items_name',255);
            $table->string('menu_items_link',255);
            $table->integer('menu_items_order')->index();
            $table->integer('page_id')->nullable();
            $table->integer('menu_id');
            $table->integer('parent_id')->index();
            $table->integer('section_id')->default(0);
            $table->integer('subsection_id')->default(0);
            $table->text('menu_items_media_info')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_items');
    }
}
