<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class RequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisitions_id',
        'items_id',
        'qty',
        'price',
        'note'
    ];

    public function requisition(){
        return $this->hasMany('App\Models\Requisition','id','requisitions_id');
    }
    
}
