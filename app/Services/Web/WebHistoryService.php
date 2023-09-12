<?php

namespace App\Services\Web;

use App\Services\Service;
use App\Services\Files\ImageUploadService;
use App\Models\Web\WebHistory;
use App\Models\Image;
use Illuminate\Support\Facades\DB;
use Exception;

class WebHistoryService extends Service
{

    public $validationRules = [
        // 'image'   =>'required|string',
        'year'    =>'required|numeric',
        'month'   =>'required|numeric',
        'content' =>'required|string',
        'language'=>'required|string',
    ];

    public $validationMsg = [
        // 'image.required' =>"01 image",
        // 'image.string'   =>"01 image",
        'year.required' =>"01 year",
        'year.string'   =>"01 year",
        'month.required' =>"01 month",
        'month.string'   =>"01 month",
        'content.required' =>"01 content",
        'content.string'   =>"01 content",
        'language.required' =>"01 language",
        'languagestring'   =>"01 language",
    ];

    public function createData($req){

        try {

            $create = WebHistory::create([
                "year"    =>$req['year'],
                "month"   =>$req['month'],
                "language"=>$req['language'],
                "content" =>$req['content'],
                "user_id" =>Service::getUserId(),
                "flag"    =>0,
            ]);
            $insert_id = $create->id;

            // 圖片儲存
            if ( strpos($req['image'], "base64") ) {
                $image_service = new ImageUploadService();
                $image_data = $req['image'];
                $image_service->addImage($image_data,'WebHistory');
                $image_id = $image_service->getId();
                $image = Image::find($image_id);
                $image->fk_id = $insert_id;
                $image->save();
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
            $WebHistory = WebHistory::find($req['id']);
            $WebHistory->language= $req['language'];
            $WebHistory->year    = $req['year'];
            $WebHistory->month   = $req['month'];
            $WebHistory->content = $req['content'];
            $WebHistory->user_id = Service::getUserId();
            $WebHistory->save();

            // 圖片儲存
            if ( strpos($req['image'], "base64") ) {
                $image = Image::where('type','=','WebHistory')->where('fk_id','=',$req['id'])->first();

                $image_service = new ImageUploadService();
                $image_data = $req['image'];

                if ( $image ) {
                    $image_service->updateImage($image_data,"WebHistory",$image->id);
                } else {
                    $image_service->addImage($image_data,'WebHistory');
                    $image_id = $image_service->getId();
                    $image = Image::find($image_id);
                    $image->fk_id = $req['id'];
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
    
    public function getlist($language)
    {
        
        try {
            // 取排序資料
            $orderBy = json_decode(file_get_contents("../resources/json/web_history.json"), true);

            $list = WebHistory::select(
                'id',
                'year',
                'month',
                'content',
                'flag as show',
                DB::raw("DENSE_RANK() over(ORDER BY year {$orderBy[0]})-1 as key1"),
                DB::raw("ROW_NUMBER() over(PARTITION BY year order by month)-1 as key2"),
                DB::raw("DATE_FORMAT(updated_at, '%Y/%m/%d %H:%i:%s') as date"),
                DB::raw('(select name from users where id = web_histories.user_id) as name'),
                DB::raw("ifnull((select url from images where fk_id = web_histories.id and type = 'WebHistory'),'') as image"),
            )
            ->where('language',$language)
            ->orderBy('year',$orderBy[0])
            ->orderBy('month','ASC')
            ->get()
            ->toArray();

            $arr = [
                "orderBy" => (boolean)$orderBy[1],
                "list"    => [],
            ];
            foreach ( $list as $k => $v ){
                $arr['list'][$v['key1']]['year'] = $v['year'];
                $arr['list'][$v['key1']]['list'][$v['key2']] = [
                    'id'     =>$v['id'],
                    'month'  =>$v['month'],
                    'content'=>$v['content'],
                    'image'  =>$v['image'],
                    'show'   =>(boolean)$v['show'],
                    'editor'=>[
                        'name'=>$v['name'],
                        'date'=>Service::dateFormat($v['date']),
                    ],
                ];
            }

            return Service::response('00', 'ok', $arr);

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

            $WebHistory = WebHistory::find($req['id']);
            $WebHistory->flag = $flag;
            $WebHistory->save();

            return Service::response('00', 'OK', '');

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    
    }

    public function updateSort($req) {
        try {

            $arr = ( $req['orderBy'] == 1 ) ? [0=>'ASC',1=>1] : [0=>'DESC',1=>0]; 

            $json = json_encode($arr);
            file_put_contents("../resources/json/web_history.json", $json); 

            return Service::response('00', 'OK', '');

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    
    }

}
