<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTakeOfficeDateToUsers extends Migration
{
    
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            
            $table->datetime('take_office_date')->comment('到職日')->nullable()->after('email');
            $table->datetime('leave_office_date')->comment('離職日')->nullable()->after('email');

        });
    }
    
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            
        });
    }
}
