<?php

namespace App\Http\Controllers\Web;

use App\Models\Web\WebNews;
use App\Models\Web\WebNewsContent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ValidatorTrait;
use App\Services\Web\WebNewsService;


class WebNewsController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new WebNewsService;
    }

    public function add(Request $request)
    {
        $req = $request->all();
        $reqCheck = $request->all();
        unset($reqCheck['content']);
        
        // 驗證資料
        $valid = $this->validData($reqCheck, $this->service->validationRules, $this->service->validationMsg);
        // $valid = $this->validData($req['content'], $this->service->validationRules2, $this->service->validationMsg2);
        if ( $valid ) { return $valid; }
       
        // 新增資料
        return $this->service->create($req);

    }

    public function update(Request $request)
    {
        $req = $request->all();

        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        // $valid = $this->validData($req['content'], $this->service->validationRules2, $this->service->validationMsg2);
        if ( $valid ) { return $valid; }

        // 更新資料
        return $this->service->update($req);
        
    }

    public function show(Request $request)
    {
        // 取明細
        return $this->service->updateFlag($request);
    }

    public function list(Request $request)
    {
        // 取列表
        return $this->service->getlist($request);
    }

    public function detail(Request $request)
    {
        // 取明細
        return $this->service->getDetail($request);
    }
}
