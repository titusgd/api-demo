<?php

namespace App\Models\Kkday;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class OrderFlight extends Model
{
    use HasFactory;

    protected $table = 'kkday_order_flights';

    protected $fillable = [
        'id',
        'kkday_order_id',
        'traffic_type',
        'arrival_airport',
        'arrival_flightType',
        'arrival_airlineName',
        'arrival_flightNo',
        'arrival_terminalNo',
        'arrival_visa',
        'arrival_date',
        'arrival_time',
        'departure_airport',
        'departure_flightType',
        'departure_airlineName',
        'departure_flightNo',
        'departure_terminalNo',
        'departure_date',
        'departure_time',
        'created_at',
        'updated_at'
    ];
}
