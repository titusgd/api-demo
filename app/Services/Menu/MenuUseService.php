<?php

namespace App\Services\Menu;

use App\Services\Service;
use App\Models\Menu\Menu;
use App\Models\Account\UserGroup;
use Illuminate\Support\Facades\DB;
// 引入Arr類別
use Illuminate\Support\Arr;

class MenuUseService extends Service
{
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
    public function validateUpdate($request, $menu_id)
    {
        $message = Arr::dot([
            'id' => [
                'required' => '01 id',
                'in' => '02 id',
                'string' => '01 id'
            ],
            'use' => [
                'required' => '01 use',
                'boolean' => '01 use'
            ]
        ]);
        $this->response = Service::validatorAndResponse(
            $request->toArray(),
            [
                'id' => 'required|string|in:menu,master,test',
                'use' => 'required|boolean'
            ],
            $message
        );
        return $this;
        // return $valid;
    }

    public function update(object $request, int $menu_id)
    {
        $show_data = Menu::select('show')->where('id', '=', $menu_id)->first();
        $show_data = json_decode($show_data['show'], true);
        $show_data[$request['id']] = $request['use'];
        $show_data = json_encode($show_data);
        Menu::where('id', '=', $menu_id)->update(['show' => $show_data]);
        $this->response = Service::response('00', 'ok');
        return $this;
        
        // ------old ------
        // $show_data = Menu::select('show')->where('id', '=', $menu_id)->first();
        // $show_data = json_decode($show_data['show'], true);
        // $show_data[$request['id']] = $request['use'];
        // $show_data = json_encode($show_data);
        // Menu::where('id', '=', $menu_id)->update(['show' => $show_data]);
        // return Service::response('00', 'ok');
    }
}
