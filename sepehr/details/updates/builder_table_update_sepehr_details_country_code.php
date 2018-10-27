<?php namespace Sepehr\Details\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSepehrDetailsCountryCode extends Migration
{
    public function up()
    {
        Schema::table('sepehr_details_country_code', function($table)
        {
            $table->boolean('is_default')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('sepehr_details_country_code', function($table)
        {
            $table->dropColumn('is_default');
        });
    }
}
