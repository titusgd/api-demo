<?php

namespace App\Services\Menu;

use App\Services\Service;
use App\Models\Menu\Menu;
use App\Models\Account\UserGroup;
use Illuminate\Support\Facades\DB;
// 引入Arr類別
use Illuminate\Support\Arr;

class MenuService extends Service
{
    private $req, $message, $rules, $id;
    // ----- createMenu -----
    // menus新增資料的預設值
    private $show = [
        'menu' => false,
        'master' => false,
        'test' => false,
    ];
    // 目錄權限預設值
    private $user_groups = [];
    //-----------------------
    private $response;
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }
    public function getResponse()
    {
        return $this->response;
    }
    public function __construct($req, $id = null)
    {
        $this->id = $id;
        $this->req = collect($req);
        $this->message = Arr::dot([
            'name' => [
                'required' => '01 name',
                'string' => '01 name',
            ],
            'short' => [
                'required' => '01 short',
                'string' => '01 short',
            ],
            'code' => [
                'required' => '01 code',
                'string' => '01 code',
            ],
            'id' => [
                'required' => '01 id',
                'integer' => '01 id',
                'exists' => '02 id',
                'unique' => '03 id'
            ]
        ]);
        // $this->message = collect()
        //     ->put('name.required', '01 name')
        //     ->put('name.string', '01 name')
        //     ->put('short.required', '01 short')
        //     ->put('short.string', '01 short')
        //     ->put('code.required', '01 code')
        //     ->put('code.string', '01 code')
        //     ->put('id.required', '01 id')
        //     ->put('id.integer', '01 id')
        //     ->put('id.exists', '02 id')
        //     ->put('id.unique', '03 id');

        $this->rules = collect()
            ->put('name', 'required|string')
            ->put('short', 'required|string')
            ->put('code', 'required|string');
    }
    public function validateStore()
    {
        (!empty($this->req->get('id'))) && $this->rules->put('id', 'integer');
        $this->response = Service::validatorAndResponse($this->req->all(), $this->rules->toArray(), $this->message);
        return $this;
        // if ($valid) return $valid;

        // (!empty($this->req->get('id'))) && $this->rules->put('id', 'integer');

        // $valid = Service::validatorAndResponse($this->req->all(), $this->rules->toArray(), $this->message);

        // if ($valid) return $valid;
    }

    public function createMenu()
    {
        $this->show['menu'] = $this->req['menu'];
        $this->show['master'] = $this->req['master'];
        $this->show['test'] = $this->req['test'];

        $menu = new Menu();
        $menu->name = $this->req->get('name');
        $menu->short = $this->req->get('short');
        $menu->code = $this->req->get('code');
        $menu->menu_id = (!empty($this->req->get('id'))) ? $this->req->get('id') : 0;
        $menu->show = json_encode($this->show);
        $menu->user_group_id = json_encode($this->user_groups);
        $menu->save();
        return $this;
    }

    public function validateUpdate()
    {
        $input_data = $this->req->all();
        $input_data['id'] = $this->id;
        $this->rules
            ->put('id', 'required|integer|exists:menus,id');

        $this->response = Service::validatorAndResponse(
            $input_data,
            $this->rules->toArray(),
            $this->message
        );
        return $this;
        // old
        // $input_data = $this->req->all();
        // $input_data['id'] = $this->id;
        // $this->rules
        //     ->put('id', 'required|integer|exists:menus,id');

        // $valid = Service::validatorAndResponse(
        //     $input_data,
        //     $this->rules->toArray(),
        //     $this->message
        // );

        // if ($valid) return $valid;
    }

    public function updateMenu()
    {
        $menu = Menu::find($this->id);
        $menu->name = $this->req->get('name');
        $menu->short = $this->req->get('short');
        $menu->code = $this->req->get('code');
        $menu->save();
        return $this;
    }

    public function list()
    {
        // json_string to array
        $fn_str_to_arr = function ($item) {
            $item = collect(json_decode($item, true))->map(function ($tt) {
                return ($tt == 'true') ? true : false;
            });
            return $item->toArray();
        };

        // 取得使用者群組列表
        $user_group_data = UserGroup::select('id', 'name')->get()->map(function ($item, $key) {
            $item['use'] = false;
            return $item;
        })->toArray();

        // 使用者群組格式化
        $fn_user_group_format = function ($item) use ($user_group_data) {
            $item = json_decode($item, true);
            $temp = $user_group_data;
            foreach ($user_group_data as $k => $v) {
                (in_array($v['id'], $item)) && $temp[$k]['use'] = true;
            }
            return $temp;
        };

        $menu = Menu::select('id', 'name', 'short', 'code', 'show', 'user_group_id as access')
            ->where('menu_id', '=', '0')
            ->with(['menu' => function ($query) {
                $query->select('id', 'menu_id', 'name', 'short', 'code', 'show', 'user_group_id as access')->with([
                    'menu' => function ($q) {
                        $q->select('id', 'menu_id', 'name', 'short', 'code', 'show', 'user_group_id as access');
                    }
                ]);
            }])
            ->get()
            ->map(function ($item, $key) use ($fn_str_to_arr, $fn_user_group_format) {

                $item['access'] = $fn_user_group_format($item['access']);
                // json_string  to array
                $item['show'] = $fn_str_to_arr($item['show']);
                $item['menu'] = $item['menu']->map(function ($item2, $key2) use ($fn_str_to_arr, $fn_user_group_format) {
                    // json_string  to array
                    $item2['access'] = $fn_user_group_format($item2['access']);
                    $item2['show'] = $fn_str_to_arr($item2['show']);
                    $item2['menu'] = $item2['menu']->map(function ($item3, $key3) use ($fn_str_to_arr, $fn_user_group_format) {
                        // json_string  to array
                        $item3['access'] = $fn_user_group_format($item3['access']);
                        $item3['show'] = $fn_str_to_arr($item3['show']);
                        // remove menu_id
                        unset($item3['menu_id']);
                        return $item3;
                    });
                    // remove menu_id
                    unset($item2['menu_id']);
                    return $item2;
                });

                return $item;
            });
        $this->response = Service::response('00', 'ok', $menu->toArray());
        return $this;
        // return Service::response('00', 'ok', $menu->toArray());
    }

    public function del()
    {
        Menu::where('id', '=', $this->id)->delete();
        return $this;
    }

    public function validateDelete()
    {
        $menu_data = Menu::where('menu_id', '=', $this->id)->get()->toArray();

        if ($menu_data) {
            $this->response =  Service::response('05', 'id');
        }
        return $this;
    }
}
