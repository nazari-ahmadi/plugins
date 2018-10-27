<?php namespace Sepehr\Service\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateSepehrServiceIndex3 extends Migration
{
    public function up()
    {
        Schema::table('sepehr_service_index', function($table)
        {
            $table->integer('status_id')->default(1)->change();
            $table->string('sender_postal_code')->change();
            $table->string('sender_address')->change();
        });
    }
    
    public function down()
    {
        Schema::table('sepehr_service_index', function($table)
        {
            $table->integer('status_id')->default(null)->change();
            $table->string('sender_postal_code', 191)->change();
            $table->string('sender_address', 191)->change();
        });
    }
}
