<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\ConnModel as Model;

class OpenTokenLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'token','ip_address'
    ];
}
