<?php namespace RainLab\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use RainLab\User\Models\User;

class UsersAddCountryCode extends Migration
{
    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->string('country_code', 10)->default('+98');
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('country_code');
        });
    }
}
