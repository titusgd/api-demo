<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

use App\Models\Hr\HumanResource;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'rank',
        'name'
    ];
}
