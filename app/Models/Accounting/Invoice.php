<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        "invoice",
        "order_id",
        "date",
        "time",
        "type",
        "invalid_date",
        "user_id",
        "total"
    ];
}
