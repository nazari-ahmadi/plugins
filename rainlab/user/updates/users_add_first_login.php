<?php namespace RainLab\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use RainLab\User\Models\User;

class UsersAddFirstLogin extends Migration
{
    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->boolean('first_login')->default(0);
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('first_login');
        });
    }
}
