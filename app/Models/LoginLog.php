<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class LoginLog extends Model
{
    use HasFactory;

    protected $fillable = [
        "input_id",
        "client_ip",
        "client_action",
        "login_status"
    ];
}
