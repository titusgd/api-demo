<?php

namespace App\Services\Cafe\Menu;

use App\Services\Service;
use App\Models\Cafe\Menu\Group;
use App\Models\Cafe\Menu\Folder;
use App\Models\Cafe\Menu\Item;
// ----- methods -----
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Validator;
// use Illuminate\Validation\ValidationData;
class GroupService extends Service
{
    private $response;

    public function getResponse()
    {
        return $this->response;
    }
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    public function validGroupName($request)
    {
        $this->response = Service::validatorAndResponse($request->all(), [
            'name' => 'required|unique:menu_groups,name'
        ], [
            'name.required' => '01 name',
            'name.unique' => '03 name'
        ]);
        return $this;
    }
    public function addGroup($request)
    {
        $group = new Group;
        $group->name = $request->name;
        $group->editor_id = auth()->user()->id;
        $group->sort = 0;
        $group->save();

        $this->response = Service::response('00', 'ok');
        return $this;
    }

    public function whereGroup()
    {
        $data = Group::select('id', 'name', DB::raw(
            '(
                select COUNT(*) from menu_folders 
                where menu_folders.menu_group_id = menu_groups.id
            )as count'
            ))->orderBy('sort','asc')->get();
        $this->response = Service::response('00', 'ok', $data);

        return $this;
    }

    public function validUpdate($request)
    {

        $this->response = Service::validatorAndResponse($request->all(), [
            'data.*.id' => 'required|exists:menu_groups,id',

        ], [
            'data.*.id.required' => '01 id',
            'data.*.id.exists' => '02 id',
        ]);
        return $this;
    }

    public function update($request)
    {
        collect($request['data'])
            ->map(function ($item) {
                $group = Group::find($item['id']);
                $group->name = $item['name'];
                $group->editor_id = auth()->user()->id;
                $group->save();
            });
        $this->response = Service::response('00', 'ok');
        return $this;
    }

    public function validDelete($group_id)
    {
        $this->response = Service::validatorAndResponse(
            ['id' => $group_id],
            [
                'id' => 'exists:menu_groups,id'
            ],
            [
                'id.exists' => '02 id',
            ]
        );
        // folder
        $folders = Folder::select('id')
            ->where('menu_group_id', '=', $group_id)
            ->get()->toArray();

        if (!empty($folders)) {
            $this->response = Service::response('05', 'id');
            return $this;
        }
        // item
        $items = Item::select('id')
            ->where('menu_group_id', '=', $group_id)
            ->get()->toArray();

        if (!empty($items)) {
            $this->response = Service::response('05', 'id');
            return $this;
        }
        return $this;
    }

    public function delete($group_id)
    {
        $group = Group::find($group_id);
        $group->delete();
        $this->response = Service::response('00', 'ok');
        return $this;
    }
    public function sort($request)
    {
        // dd($request->all());
        $data = collect($request['data']);
        $data->map(function ($item, $key) {
                Group::where('id', $item)->update(['sort' => $key + 1]);
            });
        // dd(Group::select('id','sort')->whereIn('id',[1,2,3])->get()->toArray());

        $this->response = Service::response('00', 'ok');
        return $this;
    }
}
