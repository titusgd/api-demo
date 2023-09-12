<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'item_id',
        'type',
        'qty',
    ];

}
