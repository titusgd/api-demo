<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class CtbcOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'terminal_id',
        'lidm',
        'purch_amt',
        'tx_type',
        'option',
        'key',
        'merchant_name',
        'auth_res_url',
        'order_detail',
        'auto_cap',
        'customize',
        'mac_string',
        'debug',
    ];
}
