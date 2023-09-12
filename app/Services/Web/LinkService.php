<?php

namespace App\Services\Web;

use App\Services\Service;
use App\Models\Web\Link;
use App\Models\Image;
use Illuminate\Support\Facades\DB;
use Exception;

class LinkService extends Service
{

    public $validationRules = [
        'code' =>'required|string|max:30|unique:links,code',
        'name' =>'required|string',
        // 'link' =>'required|string',
    ];

    public $validationMsg = [
        // 'link.required' =>"01 link",
        // 'link.string'   =>"01 link",
        'name.required' =>"01 name",
        'name.string'   =>"01 name",
        'code.required' =>"01 code",
        'code.string'   =>"01 code",
        'code.max'      =>"01 code",
        'code.unique'   =>"01 code",
    ];

    public function createData($req){

        try {

            $create = Link::create([
                "name"   =>$req['name'],
                "link"   =>$req['link'],
                "code"   =>$req['code'],
                "user_id"=>Service::getUserId(),
            ]);
            $insert_id = $create->id;

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
            $HomeSliderShow = Link::find($req['id']);
            $HomeSliderShow->name = $req['name'];
            $HomeSliderShow->link = $req['link'];
            $HomeSliderShow->code = $req['code'];
            $HomeSliderShow->user_id = Service::getUserId();
            $HomeSliderShow->save();

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
            $list = Link::select(
                'id',
                'code',
                'name',
                'link',
                DB::raw("DATE_FORMAT(updated_at, '%Y/%m/%d %H:%i:%s') as date"),
                DB::raw('(select name from users where id = links.user_id) as user'),
            )->get()->toArray();

            foreach ( $list as $k => $v ){
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

}
