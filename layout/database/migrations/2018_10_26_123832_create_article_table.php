<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article', function (Blueprint $table) {
            $table->increments('cms_article_id');
			$table->integer('np_article_id')->unique();
			$table->text('article_name')->nullable();
			$table->text('article_title');
			$table->text('article_headline')->nullable();
			$table->text('article_subtitle')->nullable();
			$table->text('article_body')->nullable();
			$table->text('article_custom_fields')->nullable();
			$table->char('cms_type',100)->nullable();
			$table->integer('author_id')->nullable(0)->index();
			$table->integer('section_id')->nullable()->index();
			$table->text('seo_meta_keywords')->nullable();
			$table->text('seo_meta_description')->nullable();
			$table->text('seo_meta_title')->nullable();
			$table->dateTime('publish_time')->nullable();
			$table->text('related_articles_ids')->nullable();
			$table->text('article_tags')->nullable();
			$table->integer('sub_section_id')->nullable()->index()->index();
			$table->integer('visit_count')->default(0);
			$table->integer('sponsored_flag')->defualt(0)->index();
			$table->integer('offer_flag')->nullable()->index();
			$table->integer('featured_article_flag')->nullable()->index();
			$table->integer('media_gallery_flag')->nullable()->index();
			$table->tinyInteger('video_gallery_flag')->nullable();
			$table->tinyInteger('highlight_flag')->nullable();
			$table->tinyInteger('top_story_flag')->nullable();
			$table->tinyInteger('is_updated')->nullable();
			$table->tinyInteger('is_old_article')->nullable();
			$table->integer('old_article_id')->nullable();
			$table->string('article_byline',255)->nullable('');
			$table->timestamp('ts');
			$table->dateTime('last_edited')->nullable();
			$table->dateTime('alt_publish_time')->nullable();
			$table->string('image_path',1000)->nullable();
			$table->string('author_name',255)->nullable();
			$table->string('section_name',255)->nullable();
			$table->string('sub_section_name',255)->nullable();
			$table->integer('slide_show')->nullable(0);
			$table->integer('visit_count_update_date')->nullable();
			$table->tinyInteger('breaking_news')->nullable()->index();
			$table->integer('old_cms_article_id')->nullable()->index();
			$table->string('permalink',512)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       // Schema::dropIfExists('articles');
    }
}
