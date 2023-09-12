<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Organize extends Model
{
    use HasFactory;

    protected $table = 'organizers';

    protected $fillable = [
        'chinese_name',
        'english_name',
        'menu_id',
        'code',
        'use'
    ];
}
