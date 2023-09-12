<?php

namespace App\Services\System;

use App\Services\Service;
use App\Traits\ResponseTrait;
use App\Traits\RulesTrait;
use App\Traits\TableMethodTrait;
use Illuminate\Support\Facades\DB;
use App\Models\System\ErrorReport as Model;
use App\Services\Files\ImageUploadService;

class ErrorReportService extends Service
{
    use ResponseTrait;
    use RulesTrait;
    use TableMethodTrait;
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
            case 'store':
                $rules = [
                    'describe' => 'required|string',
                    'os' => 'required|string',
                    'browser' => 'required|string',
                    'url' => 'required|string',
                    'image' => 'required|string',
                    'size' => 'required|string',

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
        $limit = ($this->request['count']) ?? 20;
        $data = Model::with(['image'])
            ->paginate($limit);

        $data_list = $data->map(function ($item) {
            return $item->formate();
        });
        
        self::setOk($data_list, [
            "total" =>$data->lastPage(),      // 總頁數
            "countTotal" => $data->total(),            // 總筆數
            "page" => $data->currentPage(),                  // 頁次
            "count" => $limit
        ]);
        return $this;
    }

    public function store()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        $model = new Model;
        $model->describe = $this->request['describe'];
        $model->os = $this->request['os'];
        $model->browser = $this->request['browser'];
        $model->error_url = $this->request['url'];
        $model->error_image = $this->request['url'];
        $model->size = $this->request['size'];
        $model->status = 1;      // 錯誤案件處理狀態
        $model->user_id = auth()->user()->id;             // 填寫錯誤案件人員
        $model->programmer_id = 0;
        $model->image_id = 0;
        $model->save();
        $image_service = new ImageUploadService();

        if (!$image_service->checkImageExtension($this->request['image'])) return Service::response("300", "file");
        // 圖片上傳與新增
        $image_service->addImage($this->request['image'], "ErrorReport", $model->id);
        // 取得圖片id
        $model->image_id = $image_service->getId();
        $model->save();
        self::setOk();
        return $this;
    }

    public function update()
    {
        if (!empty(self::getResponse())) return $this;
        //code ... ...
        $model = Model::find($this->dataId);
        $model->save();
        self::setOk();
        return $this;
    }

    public function destroy()
    {
        if (!empty(self::getResponse())) return $this;
        self::delAndReSort(
            (new Model),
            $this->dataId,
            'id'
        );
        self::setOk();
        return $this;
    }
    public function settingSort()
    {
        if (!empty(self::getResponse())) return $this;
        self::sort((new Model), $this->request['data'], 'id');
        self::setOk();
        return $this;
    }
    public function settingUse()
    {
        if (!empty(self::getResponse())) return $this;
        self::use((new Model), $this->request['id'], $this->request['use']);
        self::setOk();
        return $this;
    }
}
