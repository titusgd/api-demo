<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class DayStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'statement_date',
        'total_receive',
        'total_pay',
        'time',
    ];

    public function day_statement_data()
    {
        return $this->hasMany('App\Models\Accounting\DayStatementData');
        // return $this->belongsToMany(App\Models\Accounting::class,)
    }
}
