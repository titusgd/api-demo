<?php

namespace App\Services\Menu;

use App\Services\Service;
use App\Models\Account\UserGroup;
use App\Models\Menu\Menu;
use Illuminate\Support\Arr;

class MenuAccessUseService extends Service
{
    public function validateUpdate($request, $menu_id)
    {
        $input_data = [
            'menu_id'=>$menu_id,
            'user_group_id'=>$request->id,
            'use'=>$request->use,
        ];

        $rules = [
            'menu_id'=>'required|integer|exists:menus,id',
            'user_group_id'=>'required|integer|exists:user_groups,id',
            'use'=>'required|boolean'
        ];
        // error message
        $message = Arr::dot([
            'menu_id'=>[
                'required'=>'01 id',
                'integer'=>'01 id',
                'exists'=>'02 id'
            ],
            'user_group_id'=>[
                'required'=>'01 id',
                'integer'=>'01 id',
                'exists'=>'02 id'
            ],
            'use'=>[
                'required'=>'01 use',
                'boolean'=>'01 use',
            ]
        ]);

        $valid = Service::validatorAndResponse($input_data, $rules, $message);
        return $valid;
    }

    public function update($request,$menu_id){
        $menu_data = Menu::find($menu_id);
        // json to array
        $user_group_id = json_decode($menu_data['user_group_id'],true);
        // change user_group_id 
        // true => push id in array,false =>remove id

        if($request->use){
            array_push($user_group_id,$request->id);
        }else{
            $user_group_id = array_diff($user_group_id,[$request->id]);
        }
        // sort by user_group_id
        sort($user_group_id);
        $user_group_id = array_unique($user_group_id);
        // write the menu
        $menu_data->user_group_id = $user_group_id;
        $menu_data->save();

        return Service::response('00', 'ok');
    }
}
