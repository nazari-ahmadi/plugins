<?php namespace Sepehr\Service\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSepehrServiceIndex5 extends Migration
{
    public function up()
    {
        Schema::table('sepehr_service_index', function($table)
        {
            $table->text('payments')->nullable();
            $table->dropColumn('payment_type_id');
        });
    }
    
    public function down()
    {
        Schema::table('sepehr_service_index', function($table)
        {
            $table->dropColumn('payments');
            $table->integer('payment_type_id')->nullable();
        });
    }
}
