<?php namespace Sepehr\Details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSepehrDetailsSpecialService extends Migration
{
    public function up()
    {
        Schema::table('sepehr_details_special_service', function($table)
        {
            $table->smallInteger('coefficient')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::table('sepehr_details_special_service', function($table)
        {
            $table->dropColumn('coefficient');
        });
    }
}
