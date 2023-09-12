<?php
namespace App\Traits;
use App\Models\Account\UserAccessSet;
use App\Models\Account\Access;
use App\Services\Service;

trait AccessTrait{
    public function access($access_id){
        $ss = new Service();

        // 檢查使用者是否有超級權限
        $user_access = UserAccessSet::select('id')
        ->where('user_id', '=',auth()->user()->id)
        ->where('access_id','=','0')->first();
        if($user_access) return ;

        // 檢查權限是否存在
        $access  =Access::find($access_id);
        if(!$access){
            // 權限不存在
            return $ss->response('999','system');
        }

        // 檢查使用者是否有權限
        $user_access = UserAccessSet::select('id')
        ->where('user_id', '=',auth()->user()->id)
        ->where('access_id','=',$access_id)->first();
        if(!$user_access){
            return $ss->response('998','access');
        }
    }

    public function access2($func_name){
        // 使用資料表或是json方式確認，權限與函式的關係

        $ss = new Service;
        // 0 超級權限，不做判斷
        $user_access = UserAccessSet::select('id')
        ->where('user_id', '=',auth()->user()->id)
        ->where('access_id','=','0')->first();
        if($user_access) return ;

        // 取出對應函式的access_id
        $path = base_path().'\app\Traits\access.json';
        $func_arr = json_decode(file_get_contents($path), true);
        $access_id =  $func_arr[$func_name]['access_id'];
        // 如果access_id為空值或null，則不設權限
        if(empty($access_id)) return ;

        // 檢查權限是否存在
        $access  =Access::find($access_id);
        if(!$access){
            return $ss->response('999','system');
        }
        // 檢查使用者是否有權限
        $user_access = UserAccessSet::select('id')
        ->where('user_id', '=',auth()->user()->id)
        ->where('access_id','=',$access_id)->first();
        if(!$user_access){
            return $ss->response('998','access');
        }
    }
}