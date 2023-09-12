<?php

namespace App\Models\Accountant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class CurrencyExchange extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'exchange',
        'name_ch',
        'code',
        'updater_id',
    ];
}
