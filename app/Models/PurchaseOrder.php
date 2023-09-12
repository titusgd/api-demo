<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'stores_id',
        'user_id',
        'code',  
        'delivery_fee',
        'note',
        'petty_cash_list_id',
        'signee_note'
    ];

    public function image(){
        return $this->hasMany('App\Models\Image','fk_id','id')->select('id','fk_id','type','url')->where('type','PurchaseOrder');
        
    }
}
