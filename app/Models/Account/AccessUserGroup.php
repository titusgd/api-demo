<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class AccessUserGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "user_groups_id",
        "accesses_id",
        "flag",
    ];
}
