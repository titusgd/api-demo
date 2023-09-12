<?php

namespace App\Models\Kkday;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class KkdayOrderMobile extends Model
{
    use HasFactory;

    /**
     * 依照下面的文字，幫我生成  fillable
     * $table->integer('kkday_order_id')->comment('關聯 kkday_orders id');
     * $table->string('mobile_model_no')->comment('手機型號');
     * $table->string('IMEI')->comment('手機IMEI');
     * $table->date('active_date')->comment('啟用日(yyyy-MM-dd)');
     *
     */
    protected $fillable = [
        'kkday_order_id',
        'mobile_model_no',
        'IMEI',
        'active_date'
    ];
}
