<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class ItemCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_id',
        'sort_by',  
    ];
}
