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
        Schema::connection($this->connection)->create('human_resource_certificates', function (Blueprint $table) {
            $table->id();
            $table->integer('hr_id')->comment('human_resources ID');
            $table->string('name')->comment('名稱');
            $table->string('number')->nullable()->comment('證照號碼');
            $table->string('place')->nullable()->comment('地點');
            $table->string('note')->nullable()->comment('備註');
            $table->integer('type_id')->nullable()->comment('類別編號');
            $table->string('type_name')->nullable()->comment('類別名稱');
            $table->string('type_code')->nullable()->comment('類別代碼');

            // 轉義 expiryDate
            $table->date('expiry_sdate')->nullable()->comment('效期開始日期');
            $table->date('expiry_edate')->nullable()->comment('效期結束日期');


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
        Schema::dropIfExists('human_resource_certificates');
    }
};
