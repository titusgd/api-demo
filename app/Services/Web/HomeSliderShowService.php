<?php

namespace App\Services\Web;

use App\Services\Service;
use App\Services\Files\ImageUploadService;
use App\Models\Web\HomeSliderShow;
use App\Models\Image;
use Illuminate\Support\Facades\DB;
use Exception;

class HomeSliderShowService extends Service
{

    public $validationRules = [
        'image'=>'string',
        'name' =>'required|string',
    ];

    public $validationMsg = [
        'image.string'  =>"01 image",
        'name.required' =>"01 name",
        'name.string'   =>"01 name",
    ];

    public function createData($req){

        try {
            $sort = HomeSliderShow::max('sort_by')+1;
            if ( $sort == '' ) { $sort = 1; }

            $create = HomeSliderShow::create([
                "name"   =>$req['name'],
                "link"   =>$req['link'],
                "flag"   =>1,
                "user_id"=>Service::getUserId(),
                "sort_by"=>$sort,
            ]);
            $insert_id = $create->id;

            // 圖片儲存
            if ( $req['image'] != '' ) {
                $image_service = new ImageUploadService();
                $image_data = $req['image'];
                $image_service->addImage($image_data,'HomeSliderShow');
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

            $HomeSliderShow = HomeSliderShow::find($req['id']);
            $HomeSliderShow->name = $req['name'];
            $HomeSliderShow->link = $req['link'];
            $HomeSliderShow->user_id = Service::getUserId();
            $HomeSliderShow->save();

            // 圖片儲存
            if ( strpos($req['image'], "base64") ) {
                $image = Image::where('type','=','HomeSliderShow')->where('fk_id','=',$req['id'])->first();

                $image_service = new ImageUploadService();
                $image_data = $req['image'];

                if ( $image ) {
                    $image_service->updateImage($image_data,"HomeSliderShow",$image->id);
                } else {
                    $image_service->addImage($image_data,'HomeSliderShow');
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
    
    public function getlist()
    {

        try {
            $list = HomeSliderShow::select(
                'id',
                'name',
                'link',
                'flag as show',
                DB::raw("DATE_FORMAT(updated_at, '%Y/%m/%d %H:%i:%s') as date"),
                DB::raw('(select name from users where id = home_slider_shows.user_id) as user'),
                DB::raw("ifnull((select url from images where fk_id = home_slider_shows.id and type = 'HomeSliderShow'),'') as image"),
            )->orderBy('sort_by')->get()->toArray();

            foreach ( $list as $k => $v ){
                $list[$k]['show'] = (boolean)$v['show'];
                $list[$k]['editor']['name'] = $v['user'];
                $list[$k]['editor']['date'] = Service::dateFormat($v['date']);
                unset($list[$k]['user']);
                unset($list[$k]['date']);
            }

            return Service::response('00', 'ok', $list);

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

            $HomeSliderShow = HomeSliderShow::find($req['id']);
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

    public function updateSort($req) {
        try {

            foreach ( $req->data as $k => $v ) {
                $HomeSliderShow = HomeSliderShow::find($v);
                $HomeSliderShow->sort_by = $k+1;   
                $HomeSliderShow->save();             
            }

            return Service::response('00', 'OK', '');

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    
    }



}
