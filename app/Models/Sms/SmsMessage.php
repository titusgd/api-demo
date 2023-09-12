<?php

namespace App\Models\Sms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class SmsMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'mobile',
        'message',
        'status',
        'emp_code'
    ];
}
