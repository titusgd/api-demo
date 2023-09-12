<?php

namespace App\Services\Ticket;

use App\Services\Service;
use App\Traits\ResponseTrait;
use App\Traits\RulesTrait;
use App\Services\Files\ImageUploadService;
use App\Models\Ticket\HotTag as Model;
// use Illuminate\Support\Facades\DB;
use App\Traits\TableMethodTrait;

class HotTagService extends Service
{
    use ResponseTrait;
    use RulesTrait;
    use TableMethodTrait;

    private $response;
    private $request;
    private $changeErrorName, $dataId;
    private $image_type = 'TicketHotTag';

    public function __construct($request, $dataId = null)
    {
        $this->dataId = $dataId;
        $this->request = collect($request);
    }

    public function runValidate($method)
    {
        switch ($method) {
            case 'store':
                $rules = [
                    "data" => "required|array",
                    "data.*" => "required|integer|unique:*.ticket_hot_tags,kkday_cat_sub_key_id"
                ];
                $data = $this->request->toArray();
                break;
            case 'uploadImage':
                $rules = [
                    "sub_key_id" => "required|integer|exists:*.ticket_hot_tags,kkday_cat_sub_key_id",
                    "image" => "required|string"
                ];
                $data = $this->request->toArray();
                break;
            case 'destroy':
                $rules = [
                    "sub_key_id" => "required|integer|exists:*.ticket_hot_tags,kkday_cat_sub_key_id",
                ];
                $data = $this->request->toArray();
                $data['sub_key_id'] = $this->dataId;
                break;
            case 'settingSort':
                $rules = [
                    "data" => "required|array",
                    "data.*" => "required|integer|exists:*.ticket_hot_tags,kkday_cat_sub_key_id"
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
        //code ... ...
        $data = Model::with(['kkdayCatSubKey', 'image'])
            ->orderBy('sort')
            ->get();
        $data = $data->map(function ($item) {
            return $item->format();
        });
        self::setOk($data);
        return $this;
    }

    public function store()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        $count = Model::count();
        foreach ($this->request['data'] as $key => $item) {
            $model = new Model;
            $model->kkday_cat_sub_key_id = $item;
            $model->image_id = 0;
            $model->sort = ++$count;
            $model->status = true;
            $model->save();
            // $count = $count++;
        }
        // $model = new Model;
        // $model->save();
        self::setOk();
        return $this;
    }

    public function update()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        // $model = Model::find($this->dataId);
        // $model->save();
        self::setOk();
        return $this;
    }

    public function destroy()
    {
        if (!empty(self::getResponse())) return $this;
        self::delAndReSort(
            (new Model),
            $this->dataId,
            'kkday_cat_sub_key_id',
            true,
            $this->image_type
        );
        self::setOk();
        return $this;
    }
    public function settingSort()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        $model = new Model;
        // self::sort($model, $this->request['data'], 'kkday_cat_sub_key_id');
        self::sort($model, $this->request['data'], 'kkday_cat_sub_key_id');
        self::setOk();
        return $this;
    }
    public function settingUse()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        self::use((new Model), $this->request['id'], $this->request['use']);
        // $model = Model::find($this->request['id']);
        // $model->status = $this->request['use'];
        // $model->save();
        self::setOk();
        return $this;
    }
    public function uploadImage()
    {
        if (!empty(self::getResponse())) return $this;
        $image_service = new ImageUploadService();

        if (!$image_service->checkImageExtension($this->request['image'])) return Service::response("300", "file");
        // TODO:圖片上傳範例
        // 刪除原圖
        $image_service->deleteImageFile($this->request['sub_key_id'], $this->image_type);
        // 圖片上傳與新增
        $image_service->addImage($this->request['image'], $this->image_type, $this->request['sub_key_id']);
        // 取得圖片id
        $image_id = $image_service->getId();
        // 更新圖片id 至 HotCity
        Model::where('kkday_cat_sub_key_id', '=',  $this->request['sub_key_id'])
            ->update(['image_id' => $image_id]);
        self::setOk();
        return $this;
    }

    public function publicIndex(){
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        $data = Model::with(['kkdayCatSubKey', 'image'])
            ->orderBy('sort')
            ->get();
        $data = $data->map(function ($item) {
            return $item->format();
        });
        self::setOk($data);
        return $this;
    }
}
