<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class ItemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'item_id',
        'safety_stock',  
    ];
}
