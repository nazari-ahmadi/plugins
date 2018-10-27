<?php namespace Sepehr\Details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSepehrDetailsCountryCode extends Migration
{
    public function up()
    {
        Schema::create('sepehr_details_country_code', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 50);
            $table->string('code', 10);
            $table->string('placeholder')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('sepehr_details_country_code');
    }
}
