<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'items_id',
        'qty',
        'price',
        'status',
        'status_date',
        'shipping_date',
        'note',
        'signee_id',
        'petty_cash_list_id',
    ];
}
