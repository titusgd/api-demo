<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class DayStatementLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', 'user_id', 'day_statement_id', 'action'
    ];
}
