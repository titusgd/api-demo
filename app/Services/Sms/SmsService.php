<?php

namespace App\Services\Sms;

use App\Services\Service;
use App\Models\Sms\SmsMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Exception;

class SmsService extends Service
{

    private $username = "70381925SMS";
    private $password = "SMS70381925";
    private $uri = "https://smsapi.mitake.com.tw/api/mtk/SmSend?CharsetURL=UTF8";
    private $uri_query = "https://smsapi.mitake.com.tw/api/mtk/SmQuery";

    public $validationRules = [
        'mobile'    => 'required|string',
        'message'   => 'required|string',
        'emp_code'  => 'required|string',
        'type' =>'required|integer',
    ];

    public $validationMsg = [
        'mobile.required'   => "01 mobile",
        'mobile.string'     => "01 mobile",
        'message.required'  => "01 message",
        'message.string'    => "01 message",
        'emp_code.required' => "01 emp_code",
        'emp_code.string'   => "01 emp_code",
        'type.required'  => "01 type",
        'type.string'    => "01 type",
    ];

    public $validationRules_main = [
        'sdate'    => 'date',
        'edate'    => 'date',
        'emp_code'   => 'string',

    ];

    public $validationMsg_main = [
        'sdate.date'     => "01 sdate",
        'edate.date'     => "01 edate",
        'emp_code.string'    => "01 emp_code",

    ];

    public function index($req)
    {

        $sdate = $req['sdate'] ?? '';
        $edate = $req['edate'] ?? '';
        $emp_code = $req['emp_code'] ?? '';

        $res = SmsMessage::where('id', '>', 0);
        if($sdate){
            $res = $res->where('created_at', '>=', $sdate);
        }
        if($edate){
            $res = $res->where('created_at', '<=', $sdate);
        }
        if($emp_code){
            $res = $res->where('emp_code', '<=', $emp_code);
        }

        $res = $res->get();

        $result = [];
        foreach ($res as $key => $value){

            $c_datetime = new Carbon($value['created_at']);
            $date_time = $c_datetime->toDateTimeString();
            $explode_datetime = explode(' ', $date_time);
            $error_msg = '';
            if($value['msg_id'] == ''){
                $value['status'] = 9;
                $error_msg = '發生錯誤';
            }

            $data =[
                'user_id' => $value['user_id'],
                'mobile' => $value['mobile'],
                'message' => $value['message'],
                'emp_code' => $value['emp_code'],
                'status' => $value['status'],
                'msg_id' => $value['msg_id'],
                'error_msg' => $error_msg,
                'account_point' => $value['account_point'],
                'created_at' => [
                    $explode_datetime[0], $explode_datetime[1]
                ]
            ];

            array_push($result, $data);
        }

        return Service::response('800', 'OK', $result);
    }

    public function create($req)
    {

        try {

            $create = SmsMessage::create([
                "mobile"   => $req["mobile"],
                "message"  => $req["message"]
            ]);
            $last_id = $create->id;

            $values = [
                "username" => $this->username,
                "password" => $this->password,
                "dstaddr"   => $req["mobile"],
                "smbody"  => $req["message"],
            ];

            $http_client = Http::asForm()->post($this->uri, $values);

            $status_code = explode("\n", $http_client->body());

            $status = explode('=', $status_code[2]);

            if($status != 9){
                $update = SmsMessage::find($last_id);
                $msg_id = explode('=', $status_code[1]);

                $account_point = explode('=', $status_code[3]);

                $update->user_id = auth()->user()->id;
                $update->type = $req['type'];
                $update->msg_id = str_replace(["\n", "\r"], "", $msg_id[1]);
                $update->account_point = str_replace(["\n", "\r"], "", $account_point[1]);
                $update->status = str_replace(["\n", "\r"], "", $status[1]);
                $update->emp_code = $req['emp_code'];
                $update->save();
            } else {
                return Service::response('999', '', '發生錯誤');
            }


            return Service::response('800', 'OK', '');
        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }
    }


    public function account_point()
    {
        try {
            $values = [
                "username" => $this->username,
                "password" => $this->password,
            ];

            $http_client = Http::asForm()->post($this->uri_query, $values);

            $point = explode('=', $http_client->body());

            $res = str_replace(["\n", "\r"], "", $point[1]);

            return Service::response('800', 'OK', $res);
        } catch (Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }
    }
}
