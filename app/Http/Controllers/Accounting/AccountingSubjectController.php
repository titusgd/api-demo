<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Services\Service;
use App\Models\AccountingSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class AccountingSubjectController extends Controller
{

    private $type = [
        0=>['id'=>1,'name'=>'營業收入'],
        1=>['id'=>2,'name'=>'營業成本'],
        2=>['id'=>3,'name'=>'營業費用'],
        3=>['id'=>4,'name'=>'非營業費用'],
        4=>['id'=>5,'name'=>'非營業收入'],
    ];

    public $Service;

    function __construct()
    {
        $this->Service = new Service;
    }

    public function list()
    {
        // 使用Eloquent 查詢並取得資料
        $results = AccountingSubject::select(
            "id",
            "code",
            "name",
            "type as class",
            "note1",
            "note2",
            "subject_id",
            "level",
            "flag",
            // DB::raw('(row_number() over( PARTITION by subject_id order by code )) - 1 as array_index'),            
        )
        ->get()
        ->toArray();

        // 重組資料
        $final_array = [];
        // 第一層
        $key = array_keys(array_column($results, 'subject_id'), 0);
        foreach( $key as $k => $v ) {
            $final_array[$k]['id']   = $results[$v]['id'];
            $final_array[$k]['code'] = $results[$v]['code'];
            $final_array[$k]['name'] = $results[$v]['name'];
            // $final_array[$k]['list'] = [];

            // 第二層
            if ( $results[$v]['flag'] == 0 ) {
                $key2 = array_keys(array_column($results, 'subject_id'), $results[$v]['id']);
                foreach( $key2 as $k2 => $v2 ) {
                    $final_array[$k]['list'][$k2]['id']   = $results[$v2]['id'];
                    $final_array[$k]['list'][$k2]['code'] = $results[$v2]['code'];
                    $final_array[$k]['list'][$k2]['name'] = $results[$v2]['name'];
                    // $final_array[$k]['list'][$k2]['list'] = [];

                    // 第三層
                    if ( $results[$v2]['flag'] == 0 ) {
                        $key3 = array_keys(array_column($results, 'subject_id'), $results[$v2]['id']);
                        foreach( $key3 as $k3 => $v3 ) {
                            $final_array[$k]['list'][$k2]['list'][$k3]['id']   = $results[$v3]['id'];
                            $final_array[$k]['list'][$k2]['list'][$k3]['code'] = $results[$v3]['code'];
                            $final_array[$k]['list'][$k2]['list'][$k3]['name'] = $results[$v3]['name'];
                            // $final_array[$k]['list'][$k2]['list'][$k3]['list'] = [];

                            // 第四層
                            if ( $results[$v3]['flag'] == 0 ) {
                                $key4 = array_keys(array_column($results, 'subject_id'), $results[$v3]['id']);
                                foreach( $key4 as $k4 => $v4 ) {
                                    
                                    // 處理類別
                                    if ( $results[$v4]['class'] != 0 ) {
                                        $class_key = array_search($results[$v4]['class'], array_column($this->type, 'id'));
            
                                        $class_array[0] = $this->type[$class_key];
                                    } else {
                                        $class_array[0] = ['id'=>0,'name'=>'無'];
                                    }

                                    $final_array[$k]['list'][$k2]['list'][$k3]['list'][$k4]['id']    = $results[$v4]['id'];
                                    $final_array[$k]['list'][$k2]['list'][$k3]['list'][$k4]['code']  = $results[$v4]['code'];
                                    $final_array[$k]['list'][$k2]['list'][$k3]['list'][$k4]['name']  = $results[$v4]['name'];
                                    $final_array[$k]['list'][$k2]['list'][$k3]['list'][$k4]['class'] = $class_array[0];
                                    $final_array[$k]['list'][$k2]['list'][$k3]['list'][$k4]['note1'] = $results[$v4]['note1'];
                                    $final_array[$k]['list'][$k2]['list'][$k3]['list'][$k4]['note2'] = $results[$v4]['note2'];
                                }
                            }
                        }
                    }
                }
            }

        }

        return $this->Service->response("00","ok", $final_array);  
    }

    public function add(Request $request)
    {  
        $req = $request->all();

        // 檢查
        $validator = Validator::make($req,[
            'name' =>'required|string|unique:accounting_subjects,name',
        ],[
            'name.required' =>"01 name",
            'name.string'   =>"01 name",
            'name.unique'   =>"03 name",
        ]);
    
        // 檢測出現錯誤
        if($validator->fails()){
            // 取得第一筆錯誤
            $message = explode(" ",$validator->errors()->first());
            return $this->Service->response($message[0],$message[1]);
        }

        // 取上一層code
        if ( $req['id'] != '' ) {

            $req['subject_id'] = $req['id'];

            $get_code = AccountingSubject::find($req['id']);
            if (!$get_code) {
                return $this->Service->response("02", "id");
            }

            $base_code= $get_code->code;
            $level    = $get_code->level+1;
            
            // 組成新代碼
            $get_code = AccountingSubject::where('subject_id',$req['id'])->max('code');
            if (!$get_code) {
                $code = $base_code . str_pad(1, strlen($base_code), 0, STR_PAD_LEFT);
            } else {
                $code = $base_code . str_pad(intval(substr($get_code, strlen($base_code))+1), strlen($base_code), 0, STR_PAD_LEFT);
            }
           
            // 有新增子科目的上一層flag更新為0
            $update = AccountingSubject::find($req['id']);
            $update->flag = 0;
            $update->save();
            
        } else {
            // 無id直接新增第一層資料
            $get_code = AccountingSubject::where('level',1)->max('code');
            if ($get_code) {
                $code  = $get_code+1;
                $level = 1;
            } else {
                $code  = 1;
                $level = 1;
            }

        }

        $req['code'] =$code;
        $req['level']=$level;
        $req = $this->Service->array_replace_key($req, "class", "type");
        $req['type'] = ( $req['type'] != "" ) ? $req['type'] : 0;


        // 寫入資料庫
        AccountingSubject::create($req);
        return $this->Service->response("00","ok");  

    }

    public function update(Request $request)
    {  
        $req = $request->all();

        // 檢查
        $validator = Validator::make($req,[
            'name' =>'required|string|unique:accounting_subjects,name,'.$req['id'],
        ],[
            'name.required' =>"01 name",
            'name.string'   =>"01 name",
            'name.unique'   =>"03 name",
        ]);
    
        // 檢測出現錯誤
        if($validator->fails()){
            // 取得第一筆錯誤
            $message = explode(" ",$validator->errors()->first());
            return $this->Service->response($message[0],$message[1]);
        }

        $class = ( $req['class'] != "" ) ? $req['class'] : 0;

        $update = AccountingSubject::find($req['id']);
        $update->name  = $req['name'];
        $update->type  = $class;
        $update->note1 = $req['note1'];
        $update->note2 = $req['note2'];
        $update->save();

        return $this->Service->response("00","ok");  
    }

    public function class() {
        return $this->Service->response("00","ok",$this->type);  
    }

    public function del(Request $request) {

        $req = $request->all();

        $chk = AccountingSubject::find($req['id']);
        if ( !$chk ) {
            return $this->Service->response("02","id");
        }

        if ( $chk->flag == 0 ) {
            return $this->Service->response("05","id");
        }

        $chk->delete();

        return $this->Service->response("00","ok");    


    }

}

