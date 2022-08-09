<?php namespace GemFourMedia\GContent\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGcontentSerie extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gcontent_serie', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->text('desc')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->boolean('featured')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gcontent_serie');
    }
}
