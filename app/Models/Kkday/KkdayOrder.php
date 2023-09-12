<?php

namespace App\Models\Kkday;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class KkdayOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'prod_no',
        'pkg_no',
        'item_no',
        's_date',
        'e_date',
        'event_time',
        'partner_order_no',
        'buyer_first_name',
        'buyer_last_name',
        'buyer_email',
        'buyer_tel_country_code',
        'buyer_tel_number',
        'buyer_country',
        'guide_lang',
        'order_note',
        'qty',
        'price',
        'discount_price',
        'markup_price',
        'total_price',
        'pay_type',
        'order_no',
        'status',
        'cancel_type',
        'cancel_desc',
        'state',
        'sku_id'
    ];
}
