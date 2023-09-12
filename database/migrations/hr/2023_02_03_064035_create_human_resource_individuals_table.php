<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    protected $connection = '*';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection($this->connection)->create('human_resource_individuals', function (Blueprint $table) {
            $table->id();
            $table->integer('hr_id')->comment('human_resources ID');
            $table->string('uid')->nullable()->comment('身分證號'); // 轉義 identityCard
            $table->date('birthday')->nullable()->comment('生日');
            $table->string('blood_type')->nullable()->comment('血型'); // 轉義 bloodType
            $table->string('phone')->nullable()->comment('手機號碼');
            $table->string('contact_tel')->nullable()->comment('聯絡電話');
            $table->string('contact_address')->nullable()->comment('聯絡地址');
            $table->string('home_tel')->nullable()->comment('聯絡電話'); // 轉義 householdRegistration
            $table->string('home_address')->nullable()->comment('聯絡地址'); // 轉義 householdRegistration
            $table->boolean('family')->default(false)->comment('是否與家人同住');
            $table->string('note')->nullable()->comment('備註');
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
        Schema::dropIfExists('human_resource_individuals');
    }
};
