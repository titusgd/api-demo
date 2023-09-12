<?php

namespace App\Models\Web;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;
use Illuminate\Support\Facades\DB;

class WebPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'language',
    ];

    public function content()
    {
        return $this->hasMany('App\Models\Web\WebPageContent');
        // ->select(
        //     'web_page_contents.id',
        //     DB::raw("case web_page_contents.type when 2 then 'image' else 'text' end as type"),
        //     DB::raw("ifnull((select url from images where fk_id = web_page_contents.id and type = 'WebPageContent'),'') as image"),
        //     'link',
        //     'web_page_contents.content'
        // );
    }
}
