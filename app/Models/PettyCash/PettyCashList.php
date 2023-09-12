<?php

namespace App\Models\PettyCash;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class PettyCashList extends Model
{
    use HasFactory;

    public $table="petty_cash_lists";
    
    protected $fillable=[
        "number",
        "date",
        "firm_id",
        "memo",
        "store_id",
        "user_id",
        "audit_id",
        "audit_date"
    ];
}
