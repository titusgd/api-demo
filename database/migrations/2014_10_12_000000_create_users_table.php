<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // $table->string('name');
            // $table->string('email')->unique();
            // $table->timestamp('email_verified_at')->nullable();
            // $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('code',16)->comment('使用者-帳號')->unique()->nullable();
            $table->string('password');
            $table->string('name',10)->comment('使用者名稱');
            $table->string('pw',12);
            $table->string('phone','10')->comment('手機');
            $table->string('tel',10)->comment('市話');
            $table->string('address',30)->comment('住址');
            $table->boolean('flag');
            $table->boolean('state');
            $table->string('note',50)->comment('備註')->default('');
            $table->string('email',50)->comment('電子郵件信箱');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
