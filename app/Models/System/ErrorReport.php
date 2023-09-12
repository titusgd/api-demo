<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;
use App\Models\Account\User;

class ErrorReport extends Model
{
    use HasFactory;
    private $status_arr = [
        0 => "pending", //：未處理
        1 => "solved", //：處理中
        2 => "processing", //：已處理
    ];
    protected $fillable = [
        'describe',
        'os',
        'browser',
        'error_url',
        'error_image',
        'size',
        'status',
        'user_id',
        'programmer_id',
    ];

    public function image()
    {
        return $this->belongsTo('App\Models\Image');
    }

    public function formate()
    {
        $user = User::where('id', '=', $this->user_id)->first();
        return [
            "id" => $this->id,
            "newer" => [
                "id" => $this->user_id,
                "name" => $user['name'],
            ],
            "date" => explode(' ', str_replace('-', '/', $this->created_at)),
            "os" => $this->os,
            "browser" => $this->browser,
            "url" => ($this->url) ?? "",
            "size" => $this->size,
            "image" => ($this->image['url']) ?? "",
            "describe" => $this->describe,
            "status" => $this->status_arr[$this->status],
        ];
    }
}
