<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class ItemGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'store_id',
        'sort_by'
    ];

}
