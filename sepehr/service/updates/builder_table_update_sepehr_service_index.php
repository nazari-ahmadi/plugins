<?php namespace Sepehr\Service\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSepehrServiceIndex extends Migration
{
    public function up()
    {
        Schema::table('sepehr_service_index', function($table)
        {
            $table->integer('status_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('sepehr_service_index', function($table)
        {
            $table->dropColumn('status_id');
        });
    }
}
