<?php namespace GemFourMedia\GContent\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGcontentCategory extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gcontent_category', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('group_id')->nullable()->unsigned();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->text('short_desc')->nullable();
            $table->text('desc')->nullable();
            $table->boolean('featured')->default(0);
            $table->boolean('published')->default(1);
            $table->integer('parent_id')->nullable();
            $table->integer('nest_left')->nullable();
            $table->integer('nest_right')->nullable();
            $table->integer('nest_depth')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gcontent_category');
    }
}
