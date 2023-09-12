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
        Schema::table('invoices', function (Blueprint $table) {
            // InvoiceID => InvoiceTransNo
            $table->integer('invoice_id')->nullable()->comment('觸發/取消預約開立');
            $table->string('invalid_number')->nullable()->comment('作廢收據編號');
            $table->string('invalid_reason')->nullable()->comment('作廢原因');
            // $table->string('invalid_no')->nullable()->comment('觸發/取消等待作廢收據編號');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
        });
    }
};
