<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class AccessGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];
}
