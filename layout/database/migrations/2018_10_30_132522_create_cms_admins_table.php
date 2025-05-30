<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCmsAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cms_admins', function (Blueprint $table) {
            $table->increments('admin_id');
            $table->string('admin_name',100)->index();
            $table->string('user_name',20)->index();
            $table->string('password',32)->index();
            $table->string('email',100)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('cms_admins');
    }
}
