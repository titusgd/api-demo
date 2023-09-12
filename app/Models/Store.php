<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Store extends Model
{
    use HasFactory;

    protected $hidden = ['pivot'];

    protected $fillable = [
        'user_id',
        'code',
        'store',
        'address',
        'phone',
        'representative',
        'use_flag',
        'floor',
        'basement',
        'week',
        'time',
        'map',
        'image_id',
        'show_web'
    ];

    public function image(){
        return $this->hasMany('App\Models\Image','fk_id','id')->select('id','fk_id','type','url')->where('type','PurchaseOrder');
        
    }
}
