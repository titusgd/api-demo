<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ValidatorTrait;
use App\Services\Web\WebPageService;


class WebPageController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new WebPageService;
    }

    public function add(Request $request)
    {
        // 驗證資料
        $valid = $this->validData($request->data, $this->service->validationRules, $this->service->validationMsg);
        if ( $valid ) { return $valid; }
       
        // 新增資料-主表
        return $this->service->createCodeData($request->data);
    }

    public function update(Request $request)
    {
        $req = $request->all();
        unset($req['content']);

        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules2, $this->service->validationMsg2);
        if ( $valid ) { return $valid; }    
      
        $valid = $this->validData($request->content, $this->service->validationRules3, $this->service->validationMsg3);
        if ( $valid ) { return $valid; }

        // 更新資料
        return $this->service->createContentData($request);
        
    }

    public function list(Request $request)
    {
        // 取列表
        return $this->service->getlist($request->language);
    }

    public function detail(Request $request)
    {
        // 取列表
        return $this->service->getdetail($request);
    }

}
