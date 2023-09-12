<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class NoticeResponse extends Model
{
    use HasFactory;
    protected $fillable = [
        'notice_id', 'user_id', 'content'
    ];
}
