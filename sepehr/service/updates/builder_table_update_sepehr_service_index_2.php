<?php namespace Sepehr\Service\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSepehrServiceIndex2 extends Migration
{
    public function up()
    {
        Schema::table('sepehr_service_index', function($table)
        {
            $table->string('sender_postal_code');
            $table->string('sender_address');
        });
    }
    
    public function down()
    {
        Schema::table('sepehr_service_index', function($table)
        {
            $table->dropColumn('sender_postal_code');
            $table->dropColumn('sender_address');
        });
    }
}
