<?php

namespace App\Http\Controllers\Web;

use App\Models\Web\WebMeta;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Web\WebMetaService;
use App\Traits\ValidatorTrait;

class WebMetaController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new WebMetaService;
    }

    public function add(Request $request)
    {
        $req = $request->all();

        // 驗證資料
        $valid = $this->validData($req['data'], $this->service->validationRules, $this->service->validationMsg);
        if ( $valid ) { return $valid; }
       
        // 新增資料
        return $this->service->createData($req);
    }

    public function update(Request $request)
    {
        $req = $request->all();

        // 驗證資料
        $valid = $this->validData($req['data'], $this->service->validationRules, $this->service->validationMsg);
        if ( $valid ) { return $valid; }

        // 更新資料
        $create = $this->service->updateData($req);
        return $create;        
    }

    public function info(Request $request)
    {
        // 取列表
        return $this->service->getlist($request->code);
    }

}
