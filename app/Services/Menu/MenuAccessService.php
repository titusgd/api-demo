<?php
namespace App\Services\Menu;
use App\Services\Service;
use App\Models\Account\UserGroup;
use App\Models\Menu\Menu;
use Illuminate\Support\Arr;

class MenuAccessService extends Service{
    public $id,$req,$message,$rules;
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

        $this->rules = collect()
            ->put('name', 'required|string')
            ->put('short', 'required|string')
            ->put('code', 'required|string');
    }
    public function validateAccessList()
    {
        $valid = Service::validatorAndResponse(
            ['id' => $this->req['id']],
            ['id' => 'required|integer|exists:menus,id'],
            [
                'id.required' => '01 id',
                'id.integer' => '01 id',
                'id.exists' => '02 id'
            ]
        );

        return $valid;
    }
    public function getAccessList()
    {
        $menu_data = Menu::select('user_group_id')
            ->where('id', '=', $this->req['id'])->first()->toArray();

        $menu_data = json_decode($menu_data['user_group_id']);

        $user_group_data = UserGroup::select('id', 'name')
            ->get()
            ->map(function ($item, $key) use ($menu_data) {
                $item['use'] = (in_array($item['id'], $menu_data)) ? true : false;
                return $item;
            })->toArray();

        return Service::response('00', 'ok', $user_group_data);
    }

    
}