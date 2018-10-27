<?php namespace Sepehr\Message\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateSepehrMessageIndex extends Migration
{
    public function up()
    {
        Schema::create('sepehr_message_index', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('description')->nullable();
            $table->integer('manager_id');
            $table->text('users');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('sepehr_message_index');
    }
}
