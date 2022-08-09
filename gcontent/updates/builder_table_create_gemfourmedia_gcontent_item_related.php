<?php namespace GemFourMedia\GContent\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGcontentItemRelated extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gcontent_item_related', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('item_id')->unsigned();
            $table->integer('related_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gcontent_item_related');
    }
}
