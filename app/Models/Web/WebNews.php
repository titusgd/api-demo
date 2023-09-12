<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;
use Illuminate\Support\Facades\DB;

class WebNews extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'language',
        'type',
        'title',
        'media',
        'date',
        'useLink',
        'link',
        'flag',
        'link_flag',
        'summary'
    ];


    public function content()
    {
        return $this->hasMany('App\Models\Web\WebNewsContent');
        // ->select(
        //     'id',
        //     DB::raw("case type when 2 then 'image' else 'text' end as type"),
        //     DB::raw("ifnull((select url from images where fk_id = web_news_contents.id and type = 'WebNewsContent'),'') as image"),
        //     'link',
        //     'content'
        // );
    }
}
