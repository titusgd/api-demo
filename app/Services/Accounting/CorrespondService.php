<?php

namespace App\Services\Accounting;

use App\Services\Service;
use Illuminate\Support\Facades\DB;
use App\Models\Accounting\AccountingCorrespond;
use Exception;


class CorrespondService extends Service
{

    public $validationRules = [
        'subject' =>'required|integer',
        'code1'   =>'required|string',
        'name'    =>'required|string',
    ];

    public $validationMsg = [
        'subject.required' =>"01 subject",
        'subject.integer'  =>"01 subject",
        'code1.required'   =>"01 code1",
        'code1.string'     =>"01 code1",
        'name.required'    =>"01 name",
        'name.string'      =>"01 name",
    ];

    // 清單
    public function getlist()
    {      
        try {

            $res = AccountingCorrespond::select(
                "id",
                "name",
                "code as code1",
                "sub_code as code2",
            )
            ->with([
                'subject'
            ])
            ->get()
            ->toArray();
            
            foreach ( $res as $k => $v) {
                $res[$k]['subject'] = $res[$k]['subject'][0];
            }

            return Service::response("00",'ok',$res);

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    

    }

    // 新增
    public function create($req)
    {

        try {
            $user_id =Service::getUserId();

            AccountingCorrespond::create([
                "name"                 =>$req['name'],
                "code"                 =>$req['code1'],
                "sub_code"             =>$req['code2'],
                "accounting_subject_id"=>$req['subject'],
                "user_id"              =>$user_id,
            ]);


            return Service::response('00', 'OK', '');

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    

    }

    // 修改
    public function update($id, $req)
    {   

        try {
            $user_id =Service::getUserId();

            $update = AccountingCorrespond::find($id);
            $update->name                  =$req['name'];
            $update->code                  =$req['code1'];
            $update->sub_code              =$req['code2'];
            $update->accounting_subject_id =$req['subject'];
            $update->user_id               =$user_id;
            $update->save(); 

            return Service::response('00', 'OK', '');

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    

    }

    // 刪除
    public function delete($id)
    {   

        try {

            AccountingCorrespond::destroy($id);

            return Service::response('00', 'OK', '');

        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode(); 
            $message .= '<br>Exception String: '. $e->__toString(); 
            
            return Service::response('999', '', $message);
        }    

    }

}