<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class TokenLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'token',
    ];
}
