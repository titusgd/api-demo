<?php

namespace App\Models\Kkday;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class OrderCar extends Model
{
    use HasFactory;

    protected $table = 'kkday_order_cars';

    protected $fillable = [
        'id',
        'kkday_order_id',
        'traffic_type',
        'is_rent_customize',
        's_location',
        'e_location',
        's_address',
        'e_address',
        's_date',
        'e_date',
        's_time',
        'e_time',
        'created_at',
        'updated_at'
    ];
}
