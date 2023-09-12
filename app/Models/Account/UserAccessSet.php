<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class UserAccessSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'access_id',
        'user_id',
        'flag',
    ];
}
