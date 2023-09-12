<?php

namespace App\Services\Ticket;

use App\Services\Service;
use App\Traits\ResponseTrait;
use App\Traits\RulesTrait;
use App\Models\Ticket\Slider;
use App\Services\Files\ImageUploadService;
use Illuminate\Support\Facades\DB;
use App\Traits\TableMethodTrait;

class SliderService extends Service
{
    use ResponseTrait;
    use RulesTrait;
    use TableMethodTrait;
    private $response;
    private $request;
    private $changeErrorName, $dataId;
    private $image_type = 'Slider'; // 圖片類別名稱

    public function __construct($request, $dataId = null)
    {
        $this->dataId = $dataId;
        $this->request = collect($request);
    }

    public function runValidate($method)
    {
        switch ($method) {
                // case 'index':
                //     $rules = [];
                //     $data = $this->request->toArray();
                //     break;
            case 'store':
                $rules = [
                    'name' => 'required|string',
                    'image' => 'nullable|string',
                    'content.type' => 'nullable|string',
                    'content.link' => 'nullable|string',
                    'content.city' => 'nullable|array',
                    'content.city.*' => 'nullable|string',
                    'content.tag.*' => 'nullable|array',
                    'content.tag.*' => 'nullable|string',
                    'content.date.from' => 'nullable|date',
                    'content.date.to' => 'nullable|date'
                ];
                $data = $this->request->toArray();
                break;
            case 'update':
                $rules = [
                    'slider_id' => 'required|integer|exists:*.ticket_sliders,id',
                    'name' => 'required|string',
                    'image' => 'nullable|string',
                    'content.type' => 'nullable|string',
                    'content.link' => 'nullable|string',
                    'content.city' => 'nullable|array',
                    'content.city.*' => 'nullable|string',
                    'content.tag.*' => 'nullable|array',
                    'content.tag.*' => 'nullable|string',
                    'content.date.from' => 'nullable|date',
                    'content.date.to' => 'nullable|date'
                ];
                $data = $this->request->toArray();
                $data['slider_id'] = $this->dataId;
                break;
            case 'destroy':
                $rules = [
                    'slider_id' => 'required|integer|exists:*.ticket_sliders,id',
                ];
                $data = $this->request->toArray();
                $data['slider_id'] = $this->dataId;
                break;
            case 'settingSort':
                $rules = [
                    'data.*' => 'required|integer|exists:*.ticket_sliders,id',
                ];
                $data = $this->request->toArray();
                self::setMessages([
                    'data.*.required' => '01 id',
                    'data.*.integer' => '01 id',
                    'data.*.exists' => '02 id'
                ]);
                break;
            case 'settingUse':
                $rules = [
                    'id' => 'required|integer|exists:*.ticket_sliders,id',
                    'use' => 'required|boolean'
                ];
                $data = $this->request->toArray();
                break;
            case 'method':
                $rules = [
                    // code
                ];
                $data = $this->request->toArray();
                break;
        }
        $this->response = self::validate($data, $rules, $this->changeErrorName);
        return $this;
    }

    public function index()
    {
        if (!empty(self::getResponse())) return $this;
        // 資料查詢
        $data = Slider::select(
            '*',
            DB::raw('(select url from images where images.id = image_id) as image')
        )
            ->orderBy('sort')
            ->get();

        $data = $data->map(function ($item) {
            return $item->dataFormat();
        });

        self::setOk($data);

        return $this;
    }

    public function store()
    {
        if (!empty(self::getResponse())) return $this;
        // 取得目前總數
        $count = Slider::count();

        $model = new Slider;
        $model->name = $this->request['name'];
        $model->image_id = 0;
        $model->editor = auth()->user()->id;
        $model->status = false;
        $model->type = $this->request['content']['type'];
        $model->link = $this->request['content']['link'];
        $model->city = json_encode($this->request['content']['city']);
        $model->tag = json_encode($this->request['content']['tag']);
        $model->date_from = $this->request['content']['date']['from'];
        $model->date_to = $this->request['content']['date']['to'];
        // 排序編號 = 當前總數 + 1(自己);
        $model->sort = $count + 1;
        $model->save();
        $image_service = new ImageUploadService();

        if (!$image_service->checkImageExtension($this->request['image'])) return Service::response("300", "file");

        // 圖片上傳與新增
        $image_service->addImage($this->request['image'], "Slider", $model->id);
        // 取得圖片id
        $image_id = $image_service->getId();
        // 更新圖片id 至 HotCity
        $model->image_id = $image_id;
        $model->save();

        self::setOk();
        return $this;
    }

    public function update()
    {
        if (!empty(self::getResponse())) return $this;
        $model = Slider::find($this->dataId);
        $model->name = $this->request['name'];
        $model->editor = auth()->user()->id;
        (!empty($this->request['content']['type'])) && $model->type = $this->request['content']['type'];
        (!empty($this->request['content']['link'])) && $model->link = $this->request['content']['link'];
        (!empty($this->request['content']['city'])) && $model->city = json_encode($this->request['content']['city']);
        (!empty($this->request['content']['tag'])) && $model->tag = json_encode($this->request['content']['tag']);
        (!empty($this->request['content']['date']['from'])) && $model->date_from = $this->request['content']['date']['from'];
        (!empty($this->request['content']['date']['to'])) && $model->date_to = $this->request['content']['date']['to'];
        $model->save();
        // 如果有圖片
        if (!empty($this->request['image'])) {
            // 圖片上傳處理

            $image_service = new ImageUploadService();
            if (!$image_service->checkImageExtension($this->request['image'])) return Service::response("300", "file");
            // 移除原圖
            $image_service->deleteImageFile($model->id, $this->image_type);
            // 圖片上傳與新增
            $image_service->addImage($this->request['image'], $this->image_type, $model->id);
            // 取得圖片id
            $image_id = $image_service->getId();
            // 更新圖片id 至 HotCity
            $model->image_id = $image_id;
            $model->save();
        }
        self::setOk();
        return $this;
    }

    public function destroy()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        // 取得之後的排序
        $del_id = Slider::select('id', 'sort')->where('id', '=', $this->dataId)->first();
        // 取得原始資料
        $data = Slider::select('id', 'sort')
            ->where('sort', '>', $del_id['sort'])
            ->orderBy('sort')
            ->get()
            ->toArray();
        // 刪除資訊
        Slider::where('id', '=', $this->dataId)->delete();
        // 刷新排序
        foreach ($data as $item) {
            $model = Slider::find($item['id']);
            $model->sort = $item['sort'] - 1;
            $model->save();
        }
        // 移除圖片
        $image_service = new ImageUploadService();
        $image_service->deleteImageFile($this->dataId, $this->image_type);
        self::setOk();
        return $this;
    }

    public function settingSort()
    {
        if (!empty(self::getResponse())) return $this;
        self::sort((new Slider), $this->request['data'], 'id');
        self::setOk();
        return $this;
    }

    public function settingUse()
    {
        if (!empty(self::getResponse())) return $this;
        self::use((new Slider), $this->request['id'], $this->request['use']);
        self::setOk();
        return $this;
    }

    public function publicIndex()
    {
        if (!empty(self::getResponse())) return $this;
        // 資料查詢
        $data = Slider::select(
            '*',
            DB::raw('(select url from images where images.id = image_id) as image'),
        )->where('status', true)
            ->orderBy('sort')
            ->get();

        $data = $data->map(function ($item) {
            return $item->dataPublicFormat();
        });

        self::setOk($data);
        return $this;
    }
}
