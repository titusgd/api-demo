<?php

namespace App\Services\Web;

use App\Services\Service;
use App\Services\Files\ImageUploadService;
use App\Models\Web\WebNews;
use App\Models\Web\WebNewsContent;
use App\Models\Image;
use Illuminate\Support\Facades\DB;
use Exception;

class WebNewsService extends Service
{

    private $type_arr = [
        1=>'mediaReport',
        2=>'news',
    ];

    public $validationRules = [
        'language'=>'required|string',
        'type'    =>'required|string',
        'title'   =>'required|string',
        'summary' =>'required|string',
        // 'media'   =>'required|string',
        'image'   =>'required|string',
        'date'    =>'required|date',
        'useLink' =>'required|boolean',
        // 'link'    =>'required|string'
    ];

    public $validationMsg = [
        'language.required'=>"01 language",
        'language.string'  =>"01 language",
        'type.required'    =>"01 type",
        'type.string'      =>"01 type",
        'title.required'   =>"01 title",
        'title.string'     =>"01 title",
        'summary.required' =>"01 summary",
        'summary.string'   =>"01 summary",
        // 'media.required'   =>"01 media",
        // 'media.string'     =>"01 media",
        'image.required'   =>"01 image",
        'image.string'     =>"01 image",
        'date.required'    =>"01 date",
        'date.string'      =>"01 date",
        'useLink.required' =>"01 useLink",
        'useLink.boolean'  =>"01 useLink",
        // 'link.required'    =>"01 link",
        // 'link.string'      =>"01 link",
    ];

    public $validationRules2 = [
        '*.type'=>'required|string',
        // '*.url' =>'required|string',
        // '*.link'=>'required|string',
        // '*.data'=>'required|string',
    ];

    public $validationMsg2 = [
        '*.type.required'=>"01 type",
        '*.type.string'  =>"01 type",
        // '*.url.required' =>"01 url",
        // '*.url.string'   =>"01 url",
        // '*.link.required'=>"01 qty",
        // '*.link.string'  =>"01 qty",
        // '*.data.required'=>"01 data",
        // '*.data.string'  =>"01 data",
    ];

    public function create($req){

        try {

            $type = ( $req['type'] == 'news' ) ? 2 : 1;

            // 主表
            $create = WebNews::create([
                "language" =>$req['language'],
                "type"     =>$type,
                "title"    =>$req['title'],
                "summary"  =>$req['summary'],
                "media"    =>$req['media'],
                "date"     =>$req['date'],
                "link_flag"=>$req['useLink'],
                "link"     =>$req['link'],
                "user_id"  =>Service::getUserId(),
                "flag"     =>0,
            ]);
            $insert_main_id = $create->id;

            // 圖片儲存
            if ( strpos($req['image'], "base64") ) {
                $image_service = new ImageUploadService();
                $image_data = $req['image'];
                $image_service->addImage($image_data,'WebNews');
                $image_id = $image_service->getId();
                $image = Image::find($image_id);
                $image->fk_id = $insert_main_id;
                $image->save();
            }

            //  次表
            foreach ( $req['content'] as $k => $v ) {
                $type = ( $v['type'] == 'image' ) ? 2 : 1;
                $create = WebNewsContent::create([
                    "web_news_id" =>$insert_main_id,
                    "type"        =>$type,
                    "link"        =>$v['link'],
                    "content"     =>$v['data'],
                ]);
                $insert_id = $create->id;

                // 圖片儲存
                if ( strpos($v['url'], "base64") ) {
                    $image_service = new ImageUploadService();
                    $image_data = $v['url'];
                    $image_service->addImage($image_data,'WebNewsContent');
                    $image_id = $image_service->getId();
                    $image = Image::find($image_id);
                    $image->fk_id = $insert_id;
                    $image->save();
                }
            }

            return Service::response('00', 'OK', '');

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    
    }

    public function update($req){

        try {

            $type = ( $req['type'] == 'news' ) ? 2 : 1;

            // 更新主表
            $update = WebNews::find($req['id']);
            $update->language = $req['language'];
            $update->type     = $type;
            $update->title    = $req['title'];
            $update->summary  = $req['summary'];
            $update->media    = $req['media'];
            $update->date     = $req['date'];
            $update->link_flag= $req['useLink'];
            $update->link     = $req['link'];
            $update->save();

            // 圖片儲存
            if ( strpos($req['image'], "base64") ) {
                $image = Image::where('type','=','WebNews')->where('fk_id','=',$req['id'])->first();

                $image_service = new ImageUploadService();
                $image_data = $req['image'];

                if ( $image ) {
                    $image_service->updateImage($image_data,"WebNews",$image->id);
                } else {
                    $image_service->addImage($image_data,'WebNews');
                    $image_id = $image_service->getId();
                    $image = Image::find($image_id);
                    $image->fk_id = $req['id'];
                    $image->save();
                }
            }

            // 刪除次表
            $id_arr = array_column($req['content'], "id");
            WebNewsContent::where('web_news_id',$req['id'])
            ->whereNotIn('id',$id_arr)
            ->delete();

            // 更新次表
            foreach ( $req['content'] as $k => $v ) {

                $type = ( $v['type'] == 'image' ) ? 2 : 1;
                if ( isset($v['id']) ) {

                    $id = $v['id'];
                    $update = WebNewsContent::find($id);
                    $update->type    = $type;
                    $update->link    = $v['link'];
                    $update->content = $v['data'];
                    $update->save();

                } else {

                    $create = WebNewsContent::create([
                        "web_news_id"=>$req['id'],
                        "type"       =>$type,
                        "link"       =>$v['link'],
                        "content"    =>$v['data'],
                    ]);
                    $id = $create->id;

                }

                // 處理圖片
                if ( strpos($v['url'], "base64") ) {
                    $image = Image::where('type','=','WebNewsContent')->where('fk_id','=',$id)->first();

                    $image_service = new ImageUploadService();
                    $image_data = $v['url'];
    
                    if ( $image ) {
                        $image_service->updateImage($image_data,"WebNewsContent",$image->id);
                    } else {
                        $image_service->addImage($image_data,'WebNewsContent');
                        $image_id = $image_service->getId();
                        $image = Image::find($image_id);
                        $image->fk_id = $id;
                        $image->save();
                    }
                } 
            }

            return Service::response('00', 'OK', '');

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    
    }

    public function updateFlag($req) {
        try {

            $flag = ( $req->show == 1 ) ? 1 : 0;

            $HomeSliderShow = WebNews::find($req['id']);
            $HomeSliderShow->flag = $flag;
            $HomeSliderShow->save();

            return Service::response('00', 'OK', '');

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    
    }
    
    public function getlist($req) {

        try {

            // 預設筆數
            $count = (!empty($req->count)) ? $req->count : 20;

            // 狀態字串轉數字
            $type = $this->getTypeKey($req->type);
    
            // 組成查詢陣列
            $where = [];
            array_push($where,['language', '=', $req->language]);
            array_push($where,['type', '=', $type]);
            if ( is_array($req->date) ) {
                if ( isset($req->date[0]) ) {
                    array_push($where,['date', '>=', $req->date[0] . " 00:00:00"]);
                }
                if ( isset($req->date[1]) ) {
                    array_push($where,['date', '<=', $req->date[1] . " 23:59:59"]);
                }
            }
 
            $list = WebNews::select(
                'id',
                'language',
                DB::raw("case when type = 2 then 'news' else 'mediaReport' end as type"),
                DB::raw("ifnull((select url from images where fk_id = web_news.id and type = 'WebNews'),'') as image"),
                'title',
                'summary',
                'date',
                'media',
                DB::raw("DATE_FORMAT(updated_at, '%Y/%m/%d %H:%i:%s') as edit_date"),
                DB::raw('(select name from users where id = web_news.user_id) as user'),
                'flag as show',
                'link_flag as useLink',
                'link'
            )
            ->where($where)
            ->orderBy('date','desc')
            ->paginate($count)
            ->toArray();
    
            // 製作page回傳格式
            $total      =$list['last_page'];    //總頁數
            $countTotal =$list['total'];        //總筆數
            $page       =$list['current_page'];  //當前頁次

            $pageinfo = [
                "total"     =>$total,      // 總頁數
                "countTotal"=>$countTotal, // 總筆數
                "page"      =>$page,       // 頁次
            ];

            foreach ( $list['data'] as $k => $v ) {
                $list['data'][$k]['date']    = Service::dateFormat($v['date']);
                $list['data'][$k]['show']    = (boolean)$v['show'];
                $list['data'][$k]['useLink'] = (boolean)$v['useLink'];
                $list['data'][$k]['editor']  = [
                    'name'=>$v['user'],
                    'date'=>Service::dateFormat($v['edit_date']),
                ];
                unset($list['data'][$k]['user']);
                unset($list['data'][$k]['edit_date']);
            }

            return Service::response_paginate("00",'ok',$list['data'],$pageinfo);

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    

    }

    

    public function getDetail($req) {        

        try {

            $list = WebNews::select(
                'id',
                'language',
                DB::raw("case when type = 2 then 'news' else 'mediaReport' end as type"),
                DB::raw("ifnull((select url from images where fk_id = web_news.id and type = 'WebNews'),'') as image"),
                'title',
                'summary',
                'date',
                'media',
                DB::raw("DATE_FORMAT(updated_at, '%Y/%m/%d %H:%i:%s') as edit_date"),
                DB::raw('(select name from users where id = web_news.user_id) as user'),
                'flag as show',
                'link_flag as useLink',
                'link'
            )
            ->with([
                'content',
            ])
            ->where('id',$req->id)
            ->get()->toArray();

            if ( !$list ) { return Service::response('02', '', []); }

            $list[0]['date']    = Service::dateFormat($list[0]['date']);
            $list[0]['show']    = (boolean)$list[0]['show'];
            $list[0]['useLink'] = (boolean)$list[0]['useLink'];
            $list[0]['editor']  = [
                'name'=>$list[0]['user'],
                'date'=>Service::dateFormat($list[0]['edit_date']),
            ];
            unset($list[0]['user']);
            unset($list[0]['edit_date']);

            foreach( $list[0]['content'] as $k => $v ) {
                $image = Image::select('url')->where('type','=','WebNewsContent')->where('fk_id','=',$v['id'])->first();
                $type = ( $v['type'] == 2 ) ? 'image' : 'text';

                $list[0]['content'][$k] = [
                    "id"   => $v['id'],
                    "type" => $type,
                    "url"  => ( $image ) ? $image->url : '',
                    "link" => $v['link'],
                    "data" => $v['content'],
                ];
            }
            return Service::response('00', 'ok', $list[0]);

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    
    }

    public function getTypeKey($value) 
    {
        return array_search($value, $this->type_arr);
    }

    public function getTypeValue($key)
    {
        return $this->type_arr[$key];
    }

}
