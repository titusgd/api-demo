<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class GroupTokenLog extends Model
{
    use HasFactory;

    protected $connection = 'group';
    protected $table = 'token_logs';
    protected $fillable = [
        'token','ip_address'
    ];
}
