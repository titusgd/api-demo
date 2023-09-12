<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class HotCity extends Model
{
    use HasFactory;

    protected $table = "ticket_hot_cities";

    protected $fillable = [
        'id',
        'kkday_city_id',
        'sort',
        'image_id'
    ];

    public function kkdayCity()
    {
        return $this->belongsTo('App\Models\Kkday\CityCode')->select('id','code','name');
    }

    public function image()
    {
        return $this->belongsTo('App\Models\Image')->select('id','url');
    }

    public function formate()
    {
        return [
            "code" => $this->kkdayCity->code,
            "name" => $this->kkdayCity->name,
            "city_id" => $this->kkday_city_id,
            "image" => (!empty($this->image->url)) ? $this->image->url : "",
        ];
    }
}
