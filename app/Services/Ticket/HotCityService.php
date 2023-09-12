<?php

namespace App\Services\Ticket;

use App\Services\Service;
use App\Traits\ResponseTrait;
use App\Traits\RulesTrait;
use App\Models\Ticket\HotCity;
use App\Services\Files\ImageUploadService;
use Illuminate\Support\Facades\DB;

class HotCityService extends Service
{
    use ResponseTrait;
    use RulesTrait;
    private $response;
    private $request;
    private $changeErrorName, $dataId;

    public function __construct($request, $dataId = null)
    {
        $this->dataId = $dataId;
        $this->request = collect($request);
    }

    public function runValidate($method)
    {
        switch ($method) {
            case "store":
                $rules = [
                    'data' => 'required|array',
                    'data.*' => 'integer|exists:*.kkday_citys,id|unique:*.ticket_hot_cities,kkday_city_id',
                ];
                $data = $this->request->toArray();
                self::setMessages([
                    'data.*.exists' => '02 data.*.city_id',
                    'data.*.unique' => '03 data.*.city_id',
                ]);
                break;
            case "destroy":
                $rules = [
                    'city_id' => 'required|exists:*.ticket_hot_cities,kkday_city_id',
                ];
                $data['city_id'] = $this->dataId;
                break;
            case 'settingStor':
                $rules = [
                    'data' => 'required|array',
                    'data.*' => 'integer|exists:*.ticket_hot_cities,kkday_city_id',
                ];
                $data = $this->request->toArray();
                self::setMessages([
                    'data.*.exists' => '02 data.*.search_city_id',
                ]);
                break;
            case 'uploadImage':
                $rules = [
                    'city_id' => 'required|exists:*.ticket_hot_cities,kkday_city_id'
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
        $data = HotCity::with(['kkdayCity', 'image'])
            ->orderBy('sort')
            ->get();

        $data = $data->map(function ($item) {
            return $item->formate();
        });
        self::setOk($data);
        return $this;
    }

    public function store()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        $data = $this->request['data'];
        foreach ($data as $key => $value) {
            $model = new HotCity();
            $model->kkday_city_id = $value;
            $model->sort = 0;
            $model->save();
        }
        $count = HotCity::count();
        HotCity::where('sort', '=', 0)->update(['sort' => $count]);
        self::setOk();
        return $this;
    }

    public function update()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        return $this;
    }

    public function destroy()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        // 取得之後的排序
        $del_id = HotCity::select('id', 'sort')->where('kkday_city_id', '=', $this->dataId)->first();
        // 取得原始資料
        $data = HotCity::select('id', 'sort')
            ->where('sort', '>', $del_id['sort'])
            ->orderBy('sort')
            ->get()
            ->toArray();
        // 刪除資訊
        HotCity::where('kkday_city_id', '=', $this->dataId)->delete();
        // 刷新排序
        foreach ($data as $item) {
            $model = HotCity::find($item['id']);
            $model->sort = $item['sort'] - 1;
            $model->save();
        }
        // 移除圖片
        $image_service = new ImageUploadService();
        $image_service->deleteImageFile($this->dataId, 'TicketHotCity');
        self::setOk();
        return $this;
    }

    public function settingStor()
    {
        if (!empty(self::getResponse())) return $this;
        // 總量
        $count = HotCity::count();
        // 全部刷為最後一個
        HotCity::where('id', '>', 0)->update(['sort' => $count]);
        // 依照陣列刷新排序
        foreach ($this->request['data'] as $key => $item) {
            HotCity::where('kkday_city_id', '=', $item)
                ->update(['sort' => ($key + 1)]);
        }
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
        $image_service->deleteImageFile($this->request['city_id'], 'TicketHotCity');
        // 圖片上傳與新增
        $image_service->addImage($this->request['image'], "TicketHotCity", $this->request['city_id']);
        // 取得圖片id
        $image_id = $image_service->getId();
        // 更新圖片id 至 HotCity
        HotCity::where('kkday_city_id', '=',  $this->request['city_id'])
            ->update(['image_id' => $image_id]);
        self::setOk();
        return $this;
    }

    public function publicIndex()
    {
        if (!empty(self::getResponse())) return $this;

        $data = HotCity::with(['kkdayCity', 'image'])
            ->orderBy('sort')
            ->get();

        $data = $data->map(function ($item) {
            return $item->formate();
        });
        
        self::setOk($data);
        return $this;
    }
}
