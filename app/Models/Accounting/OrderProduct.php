<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class OrderProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        "order_id",
        "name",
        "price",
    ];
}
