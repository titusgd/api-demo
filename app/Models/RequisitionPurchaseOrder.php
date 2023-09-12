<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class RequisitionPurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_item_id',
        'purchase_order_id',
        'purchase_order_item_id'
    ];

}
