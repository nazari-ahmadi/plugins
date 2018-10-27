<?php namespace RainLab\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function ($table) {
            $table->string('wallet_charge')->default(0);
        });
    }

    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('wallet_charge');
        });
    }

}
