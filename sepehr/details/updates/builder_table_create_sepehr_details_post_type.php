<?php namespace Sepehr\Details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSepehrDetailsPostType extends Migration
{
    public function up()
    {
        Schema::create('sepehr_details_post_type', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 50);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('sepehr_details_post_type');
    }
}
