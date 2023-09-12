<?php

namespace App\Models\PettyCash;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class PettyCashListItem extends Model
{
    use HasFactory;

    protected $fillable =[
        "summary",
        'qty',
        'price',
        'petty_cash_list_id'
    ];
}
