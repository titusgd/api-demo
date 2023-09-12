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
        Schema::create('kkday_product_details', function (Blueprint $table) {
            $table->id();
            $table->integer('prod_no')->comment('產品編號');
            $table->boolean("is_cancel_free")->default(false);
            $table->boolean("is_tour")->default(false);
            $table->string("timezone")->default("Asia/Taipei");
            $table->boolean("confirm_order_time")->default(false);
            $table->boolean("is_translate_complete")->default(false);
            $table->boolean("have_translate")->default(false);
            $table->string("inquiry_locale")->default("zh-tw");
            $table->boolean("is_all_sold_out")->default(false);
            $table->decimal("b2c_min_price",12, 2)->default(0.00);
            $table->decimal("b2b_min_price", 12, 2)->default(0.00);
            $table->decimal("avg_rating_star", 12, 2)->default(0.00);
            $table->boolean("instant_booking")->default(false);
            $table->integer("order_count")->default(0);
            $table->integer("days")->default(0);
            $table->integer("hours")->default(0);
            $table->integer("duration")->default(0);
            $table->string("introduction")->nullable();
            $table->decimal("b2c_price", 12, 2)->default(0.00);
            $table->decimal("b2b_price", 12, 2)->default(0.00);
            $table->string("prod_currency")->default("TWD");
            $table->integer("pkg_no")->nullable();
            $table->string("pkg_name")->nullable();
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
        Schema::dropIfExists('kkday_product_details');
    }
};
