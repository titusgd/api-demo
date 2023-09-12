<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class HotTag extends Model
{
    use HasFactory;

    protected $table = 'ticket_hot_tags';

    protected $fillable = [
        'kkday_cat_sub_key_id',
        'image_id',
        'sort',
        'status',
    ];

    public function kkdayCatSubKey()
    {
        return $this
            ->belongsTo('App\Models\Kkday\CatKey\CatSubKey')
            ->select('id', 'type', 'description_ch');
    }

    public function image()
    {
        return $this
            ->belongsTo('App\Models\Image')
            ->select('id', 'url');
    }

    public function format()
    {
        return [
            "sub_key_id" => $this->kkdayCatSubKey->id,
            "code" => $this->kkdayCatSubKey->type,
            "description" => $this->kkdayCatSubKey['description_ch'],
            "image" => (!empty($this->image->url)) ? $this->image->url : "",
        ];
    }
}
