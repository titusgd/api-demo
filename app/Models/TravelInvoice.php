<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class TravelInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_order_no',
        'category',
        'status',
        'buyer_name',
        'buyer_ubn',
        'buyer_address',
        'buyer_email',
        'buyer_phone',
        'seller_name',
        'total_amt',
        'email_lang',
        'item_name',
        'item_count',
        'item_unit',
        'item_price',
        'item_amt',
        'tour_name',
        'tour_no',
        'tour_date',
        'tax_noted',
        'comment',
        'create_status_time',
        'create_statusadd',
        'invoice_number',
        'invoice_trans_no',
        'random_num',
        'check_code',
        'surplus',
        'display_url',
    ];
}
