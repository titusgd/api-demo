<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'order_no',
        'partner_order_no',
        'contact_name',
        'contact_email',
        'contact_phone',
        'contact_tel',
        'on_line',
        'status',
        'cancel_type',
        'cancel_desc',
        'amount'
    ];
}
