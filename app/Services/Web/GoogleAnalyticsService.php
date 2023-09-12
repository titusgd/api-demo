<?php

namespace App\Services\Web;

use App\Services\Service;
use App\Models\Web\GoogleAnalytics;
use Illuminate\Support\Facades\DB;
use Exception;

class GoogleAnalyticsService extends Service
{

    public $validationRules = [
        'code' =>'required|string|max:30',
    ];

    public $validationMsg = [
        'code.required' =>"01 code",
        'code.string'   =>"01 code",
        'code.max'      =>"01 code",
    ];

    public function updateData($req){

        try {

            // 清空舊資料
            GoogleAnalytics::truncate();


            foreach ( $req->code as $k => $v ) {
                $create = GoogleAnalytics::create([
                    "code"   =>$v,
                    "user_id"=>Service::getUserId(),
                ]);                
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
            $list = GoogleAnalytics::select(
                'code',
                DB::raw("DATE_FORMAT(updated_at, '%Y/%m/%d %H:%i:%s') as date"),
                DB::raw('(select name from users where id = google_analytics.user_id) as user'),
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
