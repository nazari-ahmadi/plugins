<?php
namespace Rantiz\Gateway\Updates;

use DB;
use Schema;
use October\Rain\Database\Updates\Migration;

class AlterIdInTransactionsTable extends Migration
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
		try {
			DB::statement("ALTER TABLE  `" . $this->getLogTable() . "` drop foreign key gateway_transactions_logs_transaction_id_foreign;");
			DB::statement("ALTER TABLE  `" . $this->getLogTable() . "` DROP INDEX gateway_transactions_logs_transaction_id_foreign;");
		} catch (Exception $e) {
			
		}	
		
		try {		
			DB::statement("update `" . $this->getTable() . "` set `payment_date`=null WHERE  `payment_date`=0;");
			DB::statement("ALTER TABLE `" . $this->getTable() . "` CHANGE `id` `id` BIGINT UNSIGNED NOT NULL;");
			DB::statement("ALTER TABLE `" . $this->getLogTable() . "` CHANGE `transaction_id` `transaction_id` BIGINT UNSIGNED NOT NULL;");
			DB::statement("ALTER TABLE  `" . $this->getLogTable() . "` ADD INDEX `gateway_transactions_logs_transaction_id_foreign` (`transaction_id`);");
		} catch (Exception $e) {
			
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement("ALTER TABLE `" . $this->getTable() . "` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL;");
	}
}
