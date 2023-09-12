<?php

namespace App\Services\Accounts;

use App\Services\Service;
use App\Models\Account\WebSetting;
use App\Models\Account\WebSettingUser;
use Illuminate\Support\Facades\DB;
// use App\Traits\JsonTrait;
// use App\Traits\AutoErrorMessageTrait;

class WebSettingService extends Service
{
    // use JsonTrait, AutoErrorMessageTrait;
    public function vali($req)
    {
        //----------------------------------------------------------------
        $relus_data = [];
        $web_setting = WebSetting::get();
        // 讀取資料庫設定檔。
        foreach ($web_setting as $key => $value) {
            $str_key = 'data.' . $value['category'] . '.' . $value['item'];
            $str_value = 'required';
            switch ($value['value_type']) {
                case 'number':
                    $str_value .= '|numeric';
                    if ($value['restriction']) $str_value .= '|' . $value['restriction'];
                    break;
                default:
                    $str_value .= '|' . $value['value_type'];
                    if ($value['restriction']) $str_value .= '|' . $value['restriction'];
                    break;
            }

            $relus_data[$str_key] = $str_value;
        }
        // 錯誤訊息生成
        $message = $this->error_message($relus_data);

        $vali = Service::validatorAndResponse($req, $relus_data, $message);
        return $vali;
    }
    // 生成錯誤訊息陣列
    public function error_message($arr)
    {
        $temp = [];
        foreach ($arr as $key => $val) {

            // 分割字串取得 最後一個參數 'dark'
            // 'data.page.dark' => ['data','page','dark']
            $title = explode(".", $key);
            // 分割字串取得 驗證條件 
            // 'required|numeric|exists:users,id' => ['required','numeric','exists:users,id']

            $value = explode("|", $val);
            foreach ($value as $key2 => $val2) {
                // 分割字串 取得驗證條件
                // 'exists:users,id'=> ['exists','users,id']
                $val_arr = explode(':', $val2);
                $str = $val_arr['0'];

                switch ($str) {
                    case 'unique':
                        $temp[$key . '.' . $str] = '03 ' . end($title);
                        break;
                    case 'exists':
                        $temp[$key . '.' . $str] = '02 ' . end($title);
                        break;
                    default:
                        $temp[$key . '.' . $str] = '01 ' . end($title);
                        break;
                }
            }
        }
        return $temp;
    }

    // 讀取json設定檔
    public function validatorUseJsonFile($req, $file_path)
    {
        $path = base_path() . '/' . $file_path;
        $json = json_decode(file_get_contents($path), true);
        $vali = Service::validatorAndResponse($req, $json['relus'], $json['message']);
        return $vali;
    }

    /** userSetting()
     *  使用者設定檔
     */
    public function userSetting($req)
    {
        $user_id = Service::getUserId();
        // 尋訪讀取每個key跟vulue
        foreach ($req['data'] as $key => $val) {
            foreach ($val as $key2 => $val2) {
                // 查詢每項id
                $web_setting_id = WebSetting::select('id')
                    ->where('category', '=', $key)
                    ->where('item', '=', $key2)
                    ->first();
                // 查詢使用者是否有設定檔
                $web_setting_user = WebSettingUser::where('user_id', '=', $user_id)
                    ->where('web_setting_id', '=', $web_setting_id['id'])
                    ->first();

                if ($web_setting_user) {
                    // 有使用者設定資料，則更新value
                    $web_setting_user->value = $val2;
                } else {
                    // 沒有使用者設定資料，新增資料
                    $web_setting_user = new WebSettingUser();
                    $web_setting_user->user_id = $user_id;
                    $web_setting_user->web_setting_id = $web_setting_id['id'];
                    $web_setting_user->value = $val2;
                }
                $web_setting_user->save();
            }
        }
        return Service::response("00", "ok");
    }

    public function getlist()
    {
        $temp_arr = [];

        // 使用者設定查詢
        $web_user = WebSettingUser::select(
            'id',
            'web_setting_id',
            'value',
            DB::raw('(select category from web_settings where web_settings.id = web_setting_users.web_setting_id) as category'),
            DB::raw('(select item from web_settings where web_settings.id = web_setting_users.web_setting_id) as item'),
            DB::raw('(select value_type from web_settings where web_settings.id = web_setting_users.web_setting_id) as value_type')
        )->where('user_id', '=', auth()->user()->id)->get();

        // 如果查無資料， 帶入預設值
        if (count($web_user) == 0) {
            $web_user = WebSetting::select('id as web_setting_id', 'category', 'item', 'value_type', 'default_value as value')->get();
        }

        $temp_arr = $this->dataFormat($web_user);

        return Service::response('00', 'ok', $temp_arr);
    }

    public function dataFormat($data){
        $temp_arr = [];
        foreach ($data as $key => $val) {
            // 資料型態設定
            switch ($val['value_type']) {
                case 'boolean':
                    $val['value'] = (bool)$val['value'];
                    break;
                case 'number':
                    $val['value'] = (float)$val['value'];
                    break;
                default:
                    $val['value'] = (string)$val['value'];
                    break;
            }
            
            $temp_arr[$val['category']][$val['item']] = $val['value'];
        }

        return $temp_arr;
    }
}
