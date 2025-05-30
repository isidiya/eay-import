<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePdfsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pdf', function (Blueprint $table) {
            $table->increments('pdf_id');
            $table->string('pdf_name',255)->nullable()->index();
            $table->string('publication_name',255)->nullable();
            $table->date('issue_date')->nullable();
            $table->integer('issue_number');
            $table->string('preview_image');
            $table->timestamp('upload_time')->useCurrent();
            $table->integer('uploaded_by');
            $table->string('uploader_ip');
            $table->integer('pdf_size');
            $table->integer('pdf_type');
            $table->integer('paid_issue');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('pdfs');
    }
}
