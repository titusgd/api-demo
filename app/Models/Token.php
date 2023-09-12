<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Token extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'expired'
    ];
}
