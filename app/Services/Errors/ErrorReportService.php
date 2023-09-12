<?php

namespace App\Services\Errors;
// ----- methods -----
use App\Services\Service;
use App\Services\Files\ImageUploadService;
// ----- models -----
use App\Models\Image;
use App\Models\ErrorReport;

class ErrorReportService extends Service
{
    private $page_count = 20;
    private $response;
    private $page_info;
    public function setPageCount($page_count)
    {
        $this->page_count = $page_count;
        return $this;
    }
    public function getPageCount()
    {
        return $this->page_count;
    }
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }
    public function getResponse()
    {
        return $this->response;
    }
    // 頁數設定
    private function setPageInfo(array $data)
    {
        $this->page_info = [
            "total"      => $data['last_page'],     // 總頁數
            "countTotal" => $data['total'],         // 總筆數
            "page"       => $data['current_page'],  // 頁次
        ];
        return $this;
    }

    private function getPageInfo()
    {
        return $this->page_info;
    }

    public function list($request)
    {
        $data = json_decode($request['data'], true);

        $limit = (!empty($data['count'])) ? $data['count'] : $this->getPageCount();
        $data_list = ErrorReport::select(
            "id",
            "created_at as date",
            "os",
            "browser",
            "error_url as url",
            "size",
            "error_image as image",
            "describe",
            "status"
        )->paginate($limit);
        $page_info = $this->setPageInfo($data_list->toArray())->getPageInfo();
        $data = $data_list->map(function ($item, $key) {
            $item['date'] = Service::dateFormat($item['date']);
            $item['status'] = Service::statusToString($item['status']);
            return $item;
        });
        $this->response = Service::response_paginate(
            '00',
            'ok',
            $data,
            $page_info
        );
        return $this;
    }

    public function validCreate($request)
    {
        $rules = [
            "describe" => "required|string",
            "os" => "required|string",
            "browser" => "required|string",
            "url" => "required|string",
            "image" => "required|string",
            "size" => "required|string"
        ];
        $message = [
            "describe.required" => "describe 01",
            "describe.string" => "describe 01",
            "os.required" => "os 01",
            "os.string" => "os 01",
            "browser.required" => "browser 01",
            "browser.string" => "browser 01",
            "url.required" => "url 01",
            "url.string" => "url 01",
            "image.required" => "image 01",
            "image.string" => "image 01",
            "size.required" => "size 01",
            "size.string" => "size 01"
        ];
        $this->response = Service::validatorAndResponse($request->toArray(), $rules, $message);
        return $this;
    }

    public function create($request)
    {
        $image_source = $request['image'];
        // base64解碼
        $image_service = new ImageUploadService();
        $image_data = $request['image'];
        $image_service->addImage($image_data, 'Error');
        $image_id = $image_service->getId();
        $image = Image::find($image_id);
        $error = ErrorReport::create([
            'describe' => $request['describe'],
            'os' => $request['os'],
            'browser' => $request['browser'],
            'error_url' => $request['url'],
            'error_image' => $image['url'],
            'size' => $request['size'],
            'status' => 1,               // 錯誤案件處理狀態
            'user_id' => auth()->user()->id,             // 填寫錯誤案件人員
            'programmer_id' => 0, // 未寫入->代表未指派給工程師處理
        ]);
        // $error_id = $service->create($data);
        $image->fk_id = $error['id'];
        $image->save();
        $this->response = Service::response('00','ok');
        return $this;
    }
    public function validUpdate($request)
    {
        $rules = [
            "describe" => "required|string",
            "os" => "required|string",
            "browser" => "required|string",
            "url" => "required|string",
            "image" => "required|string",
            "size" => "required|string"
        ];
        $message = [
            "describe.required" => "describe 01",
            "describe.string" => "describe 01",
            "os.required" => "os 01",
            "os.string" => "os 01",
            "browser.required" => "browser 01",
            "browser.string" => "browser 01",
            "url.required" => "url 01",
            "url.string" => "url 01",
            "image.required" => "image 01",
            "image.string" => "image 01",
            "size.required" => "size 01",
            "size.string" => "size 01"
        ];
        $this->response = Service::validatorAndResponse($request->toArray(), $rules, $message);
        return $this;
    }
}
