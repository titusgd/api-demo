<?php

namespace App\Services\Web;

use App\Services\Service;
use App\Models\Web\Video;
use Illuminate\Support\Facades\DB;
use Exception;

class VideoService extends Service
{

    public $validationRules = [
        'language'=>'string',
        'link'    =>'required|string',
        'name'    =>'required|string',
    ];

    public $validationMsg = [
        'language.string'=>"01 language",
        'link.required'  =>"01 link",
        'link.string'    =>"01 link",
        'name.required'  =>"01 name",
        'name.string'    =>"01 name",
    ];

    public function createData($req){

        try {

            $sort = Video::max('sort_by')+1;
            if ( $sort == '' ) { $sort = 1; }

            Video::create([
                "language"=>$req['language'],
                "name"    =>$req['name'],
                "link"    =>$req['link'],
                "flag"    =>1,
                "user_id" =>Service::getUserId(),
                "sort_by" =>$sort,
            ]);

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

            $HomeSliderShow = Video::find($req['id']);
            $HomeSliderShow->language = $req['language'];
            $HomeSliderShow->name     = $req['name'];
            $HomeSliderShow->link     = $req['link'];
            $HomeSliderShow->user_id  = Service::getUserId();
            $HomeSliderShow->save();

            return Service::response('00', 'OK', '');

        } catch (Exception $e) {

            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);

        }    
    }
    
    public function getlist($req)
    {

        if ( !isset($req->language) && $req->language == '' ) {
            return Service::response('101', '', );
        }
        $count = ( isset($req->count) ) ? $req->count : 100000;

        try {
            $list = Video::select(
                'id',
                'name',
                'link',
                'flag as show',
                DB::raw("DATE_FORMAT(updated_at, '%Y/%m/%d %H:%i:%s') as date"),
                DB::raw('(select name from users where id = videos.user_id) as user'),
            )
            ->where("language","=",$req->language)
            ->orderBy('sort_by')
            ->paginate($count)->toArray();
    
            // 製作page回傳格式
            $total      =$list['last_page'];    //總頁數
            $countTotal =$list['total'];        //總筆數
            $page       =$list['current_page'];  //當前頁次

            $pageinfo = [
                "total"     =>$total,      // 總頁數
                "countTotal"=>$countTotal, // 總筆數
                "page"      =>$page,       // 頁次
            ];
            

            foreach ( $list['data'] as $k => $v ){
                $list['data'][$k]['show'] = (boolean)$v['show'];
                $list['data'][$k]['editor']['name'] = $v['user'];
                $list['data'][$k]['editor']['date'] = Service::dateFormat($v['date']);
                unset($list['data'][$k]['user']);
                unset($list['data'][$k]['date']);
            }
            return Service::response_paginate("00",'ok',$list['data'],$pageinfo);

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

            $HomeSliderShow = Video::find($req['id']);
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
                $HomeSliderShow = Video::find($v);
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
