<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class LinePayOrderPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_main_id',
        'order_id',
        'amount',
    ];
}
