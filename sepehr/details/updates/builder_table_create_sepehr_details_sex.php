<?php namespace Sepehr\Details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSepehrDetailsSex extends Migration
{
    public function up()
    {
        Schema::create('sepehr_details_sex', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 10);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('sepehr_details_sex');
    }
}
