<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class AccountingSubject extends Model
{
    use HasFactory;

    protected $hidden = ['pivot'];

    protected $fillable = [
        'user_id',
        'subject_id',
        'code',
        'name',
        'type',
        'flag',
        'note1',
        'note2',
        'level',
    ];

}
