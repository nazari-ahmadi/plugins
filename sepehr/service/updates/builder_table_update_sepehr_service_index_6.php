<?php namespace Sepehr\Service\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSepehrServiceIndex6 extends Migration
{
    public function up()
    {
        Schema::table('sepehr_service_index', function($table)
        {
            $table->boolean('payment_status')->nullable()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('sepehr_service_index', function($table)
        {
            $table->dropColumn('payment_status');
        });
    }
}
