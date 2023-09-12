<?php

namespace App\Services\Cafe\Menu;
// ----- models -----
use App\Models\Cafe\Menu\Folder;
use App\Models\Cafe\Menu\Item;
// ----- methods -----
use App\Services\Service;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FolderService extends Service
{
    use ResponseTrait;
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function validateStore(): self
    {
        $rules = [
            'id' => 'required|exists:menu_groups,id',
            'name' => 'required|string'
        ];
        $messages = [
            'id.required' => '01 id',
            'id.exists' => '02 id',
            'name.required' => '01 name',
            'name.string' => '01 name'
        ];
        $this->setResponse(
            Service::validatorAndResponse(
                $this->request->all(),
                $rules,
                $messages
            )
        );
        return $this;
    }
    /**addFolder新增資料夾資料
     * 
     */
    public function addFolder(): self
    {
        Folder::create([
            'menu_group_id' => $this->request->get('id'),
            'name' => $this->request->get('name'),
            'editor_id' => auth()->user()->id,
        ]);
        $this->setResponse(Service::response('00', 'ok'));
        return $this;
    }
    /**validateIndex 驗證列表請求資料
     */
    public function validateIndex(): self
    {
        $query_data = json_decode($this->request->query('data'), true);
        $data['id'] = $query_data['id'];

        $rules = [
            'id' => 'required|exists:menu_groups,id'
        ];
        $messages = [
            'id.required' => '01 id',
            'id.exists' => '02 id',
        ];
        $this->setResponse(Service::validatorAndResponse(
            // $this->request->all(),
            $data,
            $rules,
            $messages
        ));
        return $this;
    }

    public function whereFolder(): self
    {
        $req = json_decode($this->request->get('data'), true);
        $menu_group_id = $req['id'];
        $folders = Folder::select(
            'id',
            'name'
        )
            ->withCount('menuItems')
            ->where('menu_group_id', '=', $menu_group_id)
            ->orderBy('sort')
            ->get();

        // $folders = Folder::select(
        //     'id',
        //     'name',
        //     DB::raw('(
        //     select COUNT(*) from menu_items 
        //     where menu_items.menu_folder_id = menu_folders.id
        //     )as count')
        // )
        //     ->where('menu_group_id', '=', $menu_group_id)
        //     ->orderBy('sort','asc')
        //     ->get();

        $this->setResponse(
            Service::response(
                '00',
                'ok',
                $folders->toArray()
            )
        );

        return $this;
    }

    public function validateUpdate($folder_id): self
    {
        $validateData = [
            'id' => $folder_id,
            'name' => $this->request->get('name')
        ];
        $rules = [
            'id' => 'required|integer|exists:menu_folders,id',
            'name' => 'required|string'
        ];
        $messages = [
            'id.required' => '01 id',
            'id.integer' => '01 id',
            'id.exists' => '02 id',
            'name.required' => '01 name',
            'name.string' => '01 name',
        ];
        $this->setResponse(Service::validatorAndResponse(
            $validateData,
            $rules,
            $messages
        ));
        return $this;
    }

    public function update($folder_id): self
    {
        Folder::where('id', '=', $folder_id)
            ->update(
                [
                    'name' => $this->request->get('name')
                ]
            );
        $this->setResponse(Service::response('00', 'ok'));
        return $this;
    }
    /**
     * validDelete 驗證delete
     * @param int $folder_id 資料夾id
     * @return self
     */
    public function validDelete(int $folder_id): self
    {
        // 驗證資料，並取得回應
        $this->response = Service::validatorAndResponse(
            ['id' => $folder_id],
            ['id' => 'required|integer|exists:menu_folders,id'],
            [
                'id.required' => '01 id',
                'id.integer' => '01 id',
                'id.exists' => '02 id',
            ]
        );
        if (!empty($this->response)) return $this;

        // 檢查是否有關連的Item資料使用中，如果有則回應錯誤
        if (Item::where('menu_folder_id', $folder_id)->exists()) {
            $this->response = Service::response('05', 'id');
            return $this;
        }
        // $item = Item::select('id')
        //     ->where('menu_folder_id', '=', $folder_id)
        //     ->get()
        //     ->toArray();

        // if (!empty($item)) {
        //     $this->response = Service::response('05', 'id');
        //     return $this;
        // }
        return $this;
    }
    /**
     * delete 刪除資料夾資料
     */
    public function delete($folder_id): self
    {
        // Folder::find($folder_id)->delete();
        // 刪除資料
        Folder::destroy($folder_id);
        //設定回應
        $this->response = Service::response('00', 'ok');
        return $this;
    }
    /**
     * sort 排序
     */
    public function sort(): self
    {
        // 取得資料
        $data = $this->request->get('data');
        // 更新資料
        foreach ($data as $key => $item) {
            Folder::where('id', $item)
                ->update(['sort' => $key + 1]);
        }
        // collect($this->request->get('data'))->map(function ($item, $key) {
        //     Folder::where('id', '=', $item)
        //         ->update(['sort' => $key + 1]);
        // });
        // 設定回應
        $this->response = Service::response('00', 'ok');
        return $this;
    }
}
