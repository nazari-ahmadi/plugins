<?php
namespace Rantiz\Gateway\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateGatewayStatusLogTable extends Migration
{

    function getTable()
    {
        return 'gateway_transactions';
    }

    function getLogTable()
    {
        return $this->getTable().'_logs';
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->getLogTable(), function ($table) {
            $table->engine="innoDB";
            $table->bigIncrements('id');
            $table->bigInteger('user_id'); 
            $table->bigInteger('transaction_id'); 
            $table->string('result_code', 10)->nullable();
            $table->string('result_message', 255)->nullable();
            $table->timestamp('log_date')->nullable();

            // $table
            //     ->foreign('transaction_id')
            //     ->references('id')
            //     ->on($this->getTable())
            //     ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->getLogTable());
    }
}
