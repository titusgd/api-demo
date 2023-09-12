<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class WebSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'item',
        'value_type',
        'restriction',
        'default_value'
    ];
}
