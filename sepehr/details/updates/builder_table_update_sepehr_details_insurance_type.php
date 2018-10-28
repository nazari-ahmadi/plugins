<?php namespace Sepehr\Details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSepehrDetailsInsuranceType extends Migration
{
    public function up()
    {
        Schema::table('sepehr_details_insurance_type', function($table)
        {
            $table->smallInteger('coefficient')->unsigned()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('sepehr_details_insurance_type', function($table)
        {
            $table->dropColumn('coefficient');
        });
    }
}
