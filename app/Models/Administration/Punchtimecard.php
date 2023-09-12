<?php

namespace App\Models\Administration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Punchtimecard extends Model
{

    use HasFactory;

    protected $fillable = [
        "id",
        "user_id",
        "date_time",
        "status",
        "os",
        "client_ip"
    ];
}
