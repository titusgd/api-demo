<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class DayStatementData extends Model
{
    use HasFactory;
    protected $fillable = [
        'day_statement_id',
        'debit_credit',
        'summary',
        'price',
        'accounting_subject_id',
        'pay_type',
        'type',
        'fk_id',
        'code'
    ];
}
