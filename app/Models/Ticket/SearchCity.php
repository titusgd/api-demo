<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class SearchCity extends Model
{
    use HasFactory;

    protected $table = "ticket_search_cities";

    protected $fillable = [
        'id',
        'kkday_city_id',
        'sort',
    ];

    public function kkdayCity()
    {
        return $this->belongsTo('App\Models\Kkday\CityCode')
            ->select('id', 'code', 'name');
    }

    public function formate()
    {
        return [
            'city_id' => $this->kkday_city_id,
            'code' => $this->kkdayCity->code,
            'name' => $this->kkdayCity->name,
        ];
    }
}
