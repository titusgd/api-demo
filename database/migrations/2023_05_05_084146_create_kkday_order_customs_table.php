<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kkday_order_customs', function (Blueprint $table) {
            $table->id();
            $table->integer('kkday_order_id')->comment('關聯 kkday_orders id');
            $table->string('cus_type')->comment('旅客類型');
            $table->string('english_last_name')->nullable()->comment('英文姓氏');
            $table->string('english_first_name')->nullable()->comment('英文名字');
            $table->string('native_last_name')->nullable()->comment('中文姓氏');
            $table->string('native_first_name')->nullable()->comment('中文名字');
            $table->string('tel_country_code')->nullable()->comment('電話國碼');
            $table->string('tel_number')->nullable()->comment('電話號碼');
            $table->string('gender')->nullable()->comment('性別');
            $table->string('contact_app')->nullable()->comment('聯絡人 app');
            $table->string('contact_app_account')->nullable()->comment('聯絡人 app 帳號');
            $table->string('country_cities')->nullable()->comment('國家城市');
            $table->string('zipcode')->nullable()->comment('郵遞區號');
            $table->string('address')->nullable()->comment('地址');
            $table->string('nationality')->nullable()->comment('國籍');
            $table->string('MTP_no')->nullable()->comment('MTP 號碼');
            $table->string('id_no')->nullable()->comment('身分證字號');
            $table->string('passport_no')->nullable()->comment('護照號碼');
            $table->string('passport_expdate')->nullable()->comment('護照有效期限');
            $table->string('birth')->nullable()->comment('生日');
            $table->string('height')->nullable()->comment('身高');
            $table->string('height_unit')->nullable()->comment('身高單位');
            $table->string('weight')->nullable()->comment('體重');
            $table->string('weight_unit')->nullable()->comment('體重單位');
            $table->string('shoe')->nullable()->comment('鞋子尺寸');
            $table->string('shoe_unit')->nullable()->comment('鞋子尺寸單位');
            $table->string('shoe_type')->nullable()->comment('鞋子類型');
            $table->string('glass_degree')->nullable()->comment('眼鏡度數');
            $table->string('meal')->nullable()->comment('餐食');
            $table->string('allergy_food')->nullable()->comment('過敏食物');
            $table->string('have_app')->nullable()->comment('是否有 app');
            $table->string('guide_lang')->nullable()->comment('導遊語言');
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
        Schema::dropIfExists('kkday_order_customs');
    }
};
