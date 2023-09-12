<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class WebSettingUser extends Model
{
    use HasFactory;

    protected $fillable = [
        "web_setting_id",
        "user_id",
        "value",
    ];
}
