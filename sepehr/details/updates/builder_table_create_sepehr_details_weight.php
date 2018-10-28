<?php namespace Sepehr\Details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSepehrDetailsWeight extends Migration
{
    public function up()
    {
        Schema::create('sepehr_details_weight', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->smallInteger('coefficient')->unsigned()->default(0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('sepehr_details_weight');
    }
}
