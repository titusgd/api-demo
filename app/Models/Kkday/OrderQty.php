<?php

namespace App\Models\Kkday;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class OrderQty extends Model
{
    use HasFactory;

    protected $table = 'kkday_order_qtys';

    protected $fillable = [
        'id',
        'kkday_order_id',
        'traffic_type',
        'CarPsg_adult',
        'CarPsg_child',
        'CarPsg_infant',
        'SafetySeat_sup_child',
        'SafetySeat_sup_infant',
        'SafetySeat_self_child',
        'SafetySeat_self_infant',
        'Luggage_carry',
        'Luggage_check',
        'created_at',
        'updated_at'
    ];
}
