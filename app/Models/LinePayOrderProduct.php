<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class LinePayOrderProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'package_id',
        'price',
        'quantity',
        'name',

    ];
}
