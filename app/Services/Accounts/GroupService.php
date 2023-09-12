<?php

namespace App\Services\Accounts;

use Illuminate\Support\Facades\Validator;
use App\Models\Account\UserGroup;
use App\Services\Service;

class GroupService
{
    private $error_message = '';
    /** crearte
     *  @param array user_group data
     *  @return 
     */
    public function create($data)
    {
        $user_group = UserGroup::create([
            'name' => $data['name'],
            'use' => $data['use'],
            'user_id' => $data['user_id'],
            'accesses_id' => $data['accesses_id'],
            'access' => $data['access']
        ]);
        $user_group_info = [
            'id' => $user_group->id,
            'use' => $user_group->use,
            'user_id' => $user_group->user_id,
            'created_at' => str_replace('-', '/', $user_group->created_at),
            'updated_at' => str_replace('-', '/', $user_group->updated_at),
        ];
        return $user_group_info;
    }

    /** isName
     *  確認code是否存在，如果存在true，不存在false。
     *  @param string $data
     *  @return boolean
     */
    public function isName($data, $id = null)
    {
        $group_list = UserGroup::select('id', 'name')->where('name', '=', $data)->first();
        return ($group_list) ? (($group_list->id == $id) ? false : true) : false;


        // if ($group_list) {
        //     return ($group_list->id == $id)? false:true;
        // } else {
        //     return false;
        // }
    }

    /** groupValidator
     * 群組字元 輸入檢查 
     * @param array $data
     * @return boolean
     */
    public function groupValidator($data)
    {
        $service = new Service();
        $valid = $service->validatorAndResponse(
            $data,
            [
                'name' => ['required', 'string', 'max:255', 'unique:user_groups'],
            ],
            [
                'unique' => '此名稱重複，名稱存在!'
            ]
        );
        if ($valid) return $valid;

        // $validator = Validator::make($data, [
        //     'name' => ['required', 'string', 'max:255', 'unique:user_groups'],
        // ], [
        //     'unique' => '此名稱重複，名稱存在!',

        // ]);
        // if ($validator->fails()) {
        //     // // 回傳laravel預設錯誤訊息格式
        //     $this->error_message = $validator->errors();
        //     return false;
        // } else {
        //     return true;
        // }
    }

    /** setErrorMessage
     *  設定錯誤訊息
     *  @param array $data
     *  @void
     */
    public function setErrorMessage($data)
    {
        $this->error_message = $data;
    }
    /** getErrorMessage
     *  取得錯誤訊息
     *  @param array $data
     *  @return array
     * 
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }
    /** checkId
     *  @param string $id 輸入待確認id
     *  @return boolean
     */
    public function checkId($id)
    {
        $user_group = UserGroup::select('id')->where('id', '=', $id)->first();
        return ($user_group) ? 1 : 0;
    }

    /**update
     * 更新資料表
     * @param array $data
     * @param string|integer $id
     * @return boolean
     */
    public function update($data, $id)
    {
        $user_g = UserGroup::where('id', '=', $id)->update($data);
        return true;
    }
}
