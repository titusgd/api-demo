<?php

namespace App\Services\Web;

use App\Services\Service;
use App\Models\Web\WebMeta;
use App\Models\Image;
use Illuminate\Support\Facades\DB;
use App\Services\Files\ImageUploadService;
use Exception;

class WebMetaService extends Service
{

    public $validationRules = [
        '*.code' =>'required|string|max:20',
    ];

    public $validationMsg = [
        '*.code.required' =>"01 code",
        '*.code.string'   =>"01 code",
        '*.code.max'      =>"01 code",
        // '*.code.unique'   =>"03 code",
    ];

    public function createData($req){

        try {

            foreach( $req['data'] as $k => $v ) {
                $create = WebMeta::create([
                    "code"       =>$v['code'],
                    "title"      =>$v['title'],
                    "description"=>$v['description'],
                    "user_id"    =>Service::getUserId(),
                ]);
                $insert_id = $create->id;

                // 圖片儲存
                if ( $v['image'] != '' ) {
                    $image_service = new ImageUploadService();
                    $image_data = $v['image'];
                    $image_service->addImage($image_data,'WebMeta');
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

    public function updateData($req){

        try {

            foreach( $req['data'] as $k => $v ) {
                $WebMeta = WebMeta::where('code',$v['code'])->first();
                if ( !$WebMeta ) { return Service::response('02', 'code', ''); }

                $WebMeta->code        = $v['code'];
                $WebMeta->title       = $v['title'];
                $WebMeta->description = $v['description'];
                $WebMeta->user_id     = Service::getUserId();
                $WebMeta->save();

                // 圖片儲存
                if ( strpos($v['image'], "base64") ) {
                    $image = Image::where('type','=','WebMeta')->where('fk_id','=',$WebMeta->id)->first();

                    $image_service = new ImageUploadService();
                    $image_data = $v['image'];

                    if ( $image ) {
                        $image_service->updateImage($image_data,"WebMeta",$image->id);
                    } else {
                        $image_service->addImage($image_data,'WebMeta');
                        $image_id = $image_service->getId();
                        $image = Image::find($image_id);
                        $image->fk_id = $WebMeta->id;
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
    
    public function getlist($code)
    {

        try {
            $list = WebMeta::select(
                'id',
                'title',
                'description',
                DB::raw("ifnull((select url from images where fk_id = web_metas.id and type = 'WebMeta'),'') as image"),
            )
            ->where('code',$code)
            ->get()
            ->toArray();

            $res = [
                'code'        => '',
                'title'       => '',
                'description' => '',
                'image'       => '',
            ];
            if ( $list ) { $res = $list[0]; }

            return Service::response('00', 'ok', $res);

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    

    }

}
