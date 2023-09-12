<?php

namespace App\Services\Cafe\Menu;

use App\Models\Cafe\Menu\Folder;
use App\Models\Cafe\Menu\Item;
use App\Models\Image;
use App\Services\Files\ImageUploadService;
use App\Services\Service;

use App\Traits\ResponseTrait;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ItemService extends Service
{
    use ResponseTrait;
    public function validateStore(Request $request): self
    {
        $rules = [
            'id' => 'integer|exists:menu_folders,id',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'use' => 'required|boolean',
        ];

        // if (!empty($request['image'])) $rules['image'] = 'image|mimes:jpeg,png,jpg,gif';
        $messages = [
            'id' => [
                'required' => '01 id',
                'integer' => '01 id',
                'exists' => '02 id',
            ],
            'name' => [
                'required' => '01 name',
                'string' => '01 name',
            ],
            'price' => [
                'required' => '01 price',
                'numeric' => '01 price',
            ],
            'image' => [
                'string' => '01 image',
                'image' => '01 image',
                'mimes' => '01 image',
            ],
            'use' => [
                'required' => '01 use',
                'boolean' => '01 use',
            ]
        ];

        $this->response = Service::validatorAndResponse(
            $request->all(),
            $rules,
            Arr::dot($messages)
        );
        if (!empty($this->response)) return $this;
        if (!empty($request->image)) {
            [$image_info, $image_data] = explode(',', $request->image);
            $image_info = preg_split('/[\/;:]/', $image_info);
            if (!in_array($image_info[2], ['jpeg', 'png', 'gif', 'jpg'])) {
                $this->response = Service::response('01', 'image');
            }
        }
        return $this;
    }

    public function addStore(Request $request): self
    {

        $folder_data = $this->getFolderData($request->id);
        $menu_item = Item::create([
            'menu_group_id' => $folder_data['menu_group_id'],
            'menu_folder_id' => $folder_data['id'],
            'name' => $request->get('name'),
            'price' => $request->get('price'),
            'accounting_subject_id' => (!empty($request->get('subject')))?$request->get('subject'):0,
            'use' => $request->get('use')
        ]);
        if (!empty($request->image)) {
            $image_source = $request->image;
            $image_service = new ImageUploadService();
            $image_data = $image_source;
            $image_service->addImage($image_data, "MenuItem", $menu_item->id);
            $image_id = $image_service->getId();
            $menu_item->image_id = $image_id;
            $menu_item->save();
        }
        $this->response = Service::response('00', 'ok');
        return $this;
    }

    private function getFolderData(int $folderId): array
    {
        return Folder::where('id', '=', $folderId)->first()->toArray();
    }

    public function validateIndex(Request $request): self
    {
        $data = json_decode($request->get('data'), true);

        $rules = ['id' => 'required|integer|exists:menu_folders,id'];
        $messages = [
            'id.required' => '01 id',
            'id.integer' => '01 id',
            'id.exists' => '02 id',
        ];
        $this->response = Service::validatorAndResponse(
            $data,
            $rules,
            $messages
        );
        return $this;
    }

    public function list(Request $request): object
    {
        $menu_folder_id = json_decode($request->get('data'), true);

        $data = Item::select(
            'id',
            'name',
            'price',
            'accounting_subject_id as subject',
            'use',
            DB::raw('(select url from images where images.id =  image_id) as image'),
            'sort'

        )->where('menu_folder_id', '=', $menu_folder_id['id'])
            ->orderBy('sort', 'asc')
            ->get();
        $this->response = Service::response('00', 'ok', $data->toArray());
        return $this;
    }

    public function validateUpdate(Request $request, int $item_id): object
    {
        $temp = collect($request->all());
        $temp->put('id', $item_id);
        $rules = [
            'id' => 'integer|exists:menu_items,id',
            'name' => 'required|string',
            'price' => 'required|numeric',
            'use' => 'required|boolean',
        ];
        $message = [
            'id' => [
                'required' => '01 id',
                'integer' => '01 id',
                'exists' => '02 id',
            ],
            'name' => [
                'required' => '01 name',
                'string' => '01 name',
            ],
            'price' => [
                'required' => '01 price',
                'numeric' => '01 price',
            ],
            'image' => [
                'string' => '01 image',
                'image' => '01 image',
                'mimes' => '01 image',
            ],
            'use' => [
                'required' => '01 use',
                'boolean' => '01 use',
            ]
        ];
        $this->response = Service::validatorAndResponse(
            $temp->toArray(),
            $rules,
            Arr::dot($message)
        );
        return $this;
    }

    public function update(object $request, int $item_id): object
    {
        $temp = collect($request->all());
        $temp
            ->put('accounting_subject_id', $request['subject'])
            ->forget('subject')
            ->forget('image');
        Item::where('id', $item_id)->update($temp->toArray());
        if (!empty($request['image'])) {
            // 刪除圖片所有訊息
            // 1.取得圖片資訊
            $menu_item = Item::select(
                DB::raw('(select path from images where images.id = image_id)as image_path'),
                'image_id'
            )->where('id', '=', $item_id)->get()->toArray();
            // 2.刪除圖片，以及資料表資訊
            if (!empty($menu_item[0]['image_path'])) {
                $path = base_path() . '/resources/' . $menu_item[0]['image_path'];
                if (file_exists($path)) {
                    unlink($path);
                    Image::where('id', '=', $menu_item[0]['image_id'])->delete();
                }
            }
            // 3. 重新上傳圖片
            $image_source = $request->image;
            $image_service = new ImageUploadService();
            $image_data = $image_source;
            $image_service->addImage($image_data, "MenuItem", $item_id);
            $image_id = $image_service->getId();

            Item::where('id', '=', $item_id)
                ->update(['image_id' => $image_id]);
        }
        $this->response = Service::response('00', 'ok');
        return $this;
    }

    public function sort(object $request): self
    {
        foreach ($request['data'] as $key => $item) {
            Item::where('id', '=', $item)->update(['sort' => ($key + 1)]);
        }
        $this->response = Service::response('00', 'ok');
        return $this;
    }
}
