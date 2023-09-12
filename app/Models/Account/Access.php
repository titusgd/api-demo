<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Access extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'user_group_id',
        "access_group_id",
        "flag",
        'user_id',
        'created_at',
        'updated_at',
    ];
}
