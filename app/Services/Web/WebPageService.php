<?php

namespace App\Services\Web;

use App\Services\Service;
use App\Services\Files\ImageUploadService;
use App\Models\Web\WebPage;
use App\Models\Web\WebPageContent;
use App\Models\Image;
use Exception;
use Illuminate\Support\Facades\DB;

class WebPageService extends Service
{

    public $validationRules = [
        '*.code'     =>'required|string',
        // '*.language' =>'required|string'
    ];

    public $validationMsg = [
        '*.code.required'     =>"01 code",
        '*.code.string'       =>"01 code",
        // '*.language.required' =>"01 language",
        // '*.language.string'   =>"01 language",
    ];

    
    public $validationRules2 = [
        'code'     =>'required|string',
        'language' =>'required|string'
    ];

    public $validationMsg2 = [
        'code.required'     =>"01 code",
        'code.string'       =>"01 code",
        'language.required' =>"01 language",
        'language.string'   =>"01 language",
    ];
    
    public $validationRules3 = [
        '*.type'     =>'required|string',
    ];

    public $validationMsg3 = [
        '*.type.required'     =>"01 type",
        '*.type.string'       =>"01 type",

    ];

    public function createCodeData($req){

        try {

            foreach ( $req as $k => $v ) {
                $chk = WebPage::where("code",$v['code'])->first();
                if ( !$chk ) {
                    WebPage::create([
                        "code"     =>$v['code'],
                        "user_id"  =>Service::getUserId(),
                    ]);
                } else {
                    $chk->code    = $v['code'];
                    $chk->user_id = Service::getUserId();
                    $chk->save();
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

    public function createContentData($req){

        try {

            // 取主表id 
            $data = WebPage::select('id')->where('code', $req->code)->get()->toArray();
            if ( !$data ) { return Service::response('02', 'code', ''); }
            
            // 刪除次表
            $id_arr = array_column($req->content, "id");
            $delete = WebPageContent::where('web_page_id',$data[0]['id'])->where('language',$req->language)
            ->whereNotIn('id',$id_arr)
            ->delete();

            // 更新次表
            foreach ( $req->content as $k => $v ) {

                $type = ( $v['type'] == 'image' ) ? 2 : 1;

                if ( isset($v['id']) ) {

                    $id = $v['id'];
                    $update = WebPageContent::find($id);
                    $update->type    = $type;
                    $update->language= $req->language;
                    $update->link    = $v['link'];
                    $update->content = $v['data'];
                    $update->sort_by = $k;
                    $update->user_id = Service::getUserId();
                    $update->save();
                } else {

                    $create = WebPageContent::create([
                        "web_page_id"=>$data[0]['id'],
                        "type"       =>$type,
                        "language"   =>$req->language,
                        "link"       =>$v['link'],
                        "content"    =>$v['data'],
                        "sort_by"    =>$k,
                        "user_id"    =>Service::getUserId(),
                    ]);
                    $id = $create->id;
                }

                // 處理圖片
                if ( strpos($v['url'], "base64") ) {
                    $image = Image::where('type','=','WebPageContent')->where('fk_id','=',$id)->first();

                    $image_service = new ImageUploadService();
                    $image_data = $v['url'];
    
                    if ( $image ) {
                        $image_service->updateImage($image_data,"WebPageContent",$image->id);
                    } else {
                        $image_service->addImage($image_data,'WebPageContent');
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
    
    public function getlist($language) {

        try {
            $list = Webpage::select(
                'id',
                'code',
            )
            ->get()->toArray();


            return Service::response('00', 'ok', $list);

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    

    }

    

    public function getDetail($req) {        

        try {

            // DB::enableQueryLog();
            $list = Webpage::select(
                'id',
                'code',
                DB::raw("'{$req->language}' as language"),
                // DB::raw("DATE_FORMAT(updated_at, '%Y/%m/%d %H:%i:%s') as date"),
                // DB::raw('(select name from users where id = web_pages.user_id) as name'),

                DB::raw("(select DATE_FORMAT(max(updated_at), '%Y/%m/%d %H:%i:%s') from `web_page_contents` where `web_page_id` = `web_pages`.`id` and `language` = '{$req->language}') as date"),
                DB::raw("(select name from users where id = (select user_id from `web_page_contents` where `web_page_id` = `web_pages`.`id` and `language` = '{$req->language}' limit 1)) as name"),
            )
            ->with(['content' => function ($query) use($req) {
                    $query->where('language',$req->language);
                    $query->orderBy('sort_by','asc');
                }
            ])
            ->where('code',$req->code)
            // ->where('language',$req->language)
            ->get()
            ->toArray();

            // dd(DB::getQueryLog());
            if ( $list ) {
                $list[0]['editor'] = [
                    "name"=>$list[0]['name'],
                    "date"=>Service::dateFormat($list[0]['date']),
                ];
                unset($list[0]['name']);
                unset($list[0]['date']);

                foreach( $list[0]['content'] as $k => $v ) {
                    $image = Image::select('url')->where('type','=','WebPageContent')->where('fk_id','=',$v['id'])->first();
                    $type = ( $v['type'] == 2 ) ? 'image' : 'text';

                    $list[0]['content'][$k] = [
                        "id"   => $v['id'],
                        "type" => $type,
                        "url"  => ( $image ) ? $image->url : '',
                        "link" => $v['link'],
                        "data" => $v['content'],
                    ];
                }
            }

            return Service::response('00', 'ok', $list[0]);

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    
    }

}
