<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class NoticeUser extends Model
{
    use HasFactory;
    protected $fillable = [
        'notice_id',
        'forwarder_id',
        'recipient_id',
        'close_type'
    ];
}
