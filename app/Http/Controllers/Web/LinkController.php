<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Web\LinkService;
use App\Traits\ValidatorTrait;

class LinkController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new LinkService;
    }

    public function add(Request $request)
    {
        $req = $request->all();

        // 驗證資料
        $valid = $this->validData($req, $this->service->validationRules, $this->service->validationMsg);
        if ( $valid ) { return $valid; }
       
        // 新增資料
        return $this->service->createData($req);
    }

    public function update(Request $request)
    {
        $req = $request->all();

        // 驗證資料
        $arr = $this->service->validationRules;
        $arr['code'] = $arr['code'] . ',' . $req['id'];
        $valid = $this->validData($req, $arr, $this->service->validationMsg);
        if ( $valid ) { return $valid; }

        // 更新資料
        $create = $this->service->updateData($req);
        return $create;        
    }

    public function list()
    {
        // 取列表
        return $this->service->getlist();
    }
}
