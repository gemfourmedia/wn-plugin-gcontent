<?php namespace GemFourMedia\GContent\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGcontentItem extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gcontent_item', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('group_id')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('serie_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('sort_order')->default(1);
            $table->string('title', 255);
            $table->string('slug', 255);
            $table->text('introtext')->nullable();
            $table->text('content')->nullable();
            $table->text('content_html')->nullable();
            $table->text('embeds')->nullable();
            $table->json('params')->nullable();
            $table->integer('hit')->default(1);
            $table->boolean('published')->default(0);
            $table->boolean('featured')->default(0);
            $table->boolean('pinned')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gcontent_item');
    }
}
