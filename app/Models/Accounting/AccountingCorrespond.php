<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class AccountingCorrespond extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "accounting_subject_id",
        "user_id",
        "code",
        "sub_code",
        "name",
    ];

    public function subject()
    {
        return $this->belongsToMany('App\Models\AccountingSubject', 'accounting_corresponds', 'id', 'accounting_subject_id')->select('accounting_subjects.id','accounting_subjects.code','accounting_subjects.name');
    }
}
