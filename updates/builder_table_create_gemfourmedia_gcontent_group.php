<?php namespace GemFourMedia\GContent\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGcontentGroup extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gcontent_group', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->text('desc')->nullable();
            $table->string('list_page')->nullable();
            $table->string('detail_page')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gcontent_group');
    }
}
