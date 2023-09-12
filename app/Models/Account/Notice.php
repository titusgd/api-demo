<?php

namespace App\Models\Account;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'title',
        'content',
        'recipient',
        'close_type',
        'forward',
        'link'
    ];

    public function response()
    {
        return $this->hasMany('App\Models\Account\NoticeResponse');
    }

    public function notice_user()
    {
        return $this->hasMany('App\Models\Account\NoticeUser');
    }

    public function users()
    {
        return $this->belongsTo('App\Models\Account\User', 'user_id', 'id');
    }
}
