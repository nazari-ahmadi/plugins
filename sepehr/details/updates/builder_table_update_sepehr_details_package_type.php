<?php namespace Sepehr\Details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSepehrDetailsPackageType extends Migration
{
    public function up()
    {
        Schema::table('sepehr_details_package_type', function($table)
        {
            $table->smallInteger('coefficient')->unsigned()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('sepehr_details_package_type', function($table)
        {
            $table->dropColumn('coefficient');
        });
    }
}
