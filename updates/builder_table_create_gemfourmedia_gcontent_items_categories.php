<?php namespace GemFourMedia\GContent\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGcontentItemsCategories extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gcontent_items_categories', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('item_id');
            $table->integer('category_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gcontent_items_categories');
    }
}
