<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Web\GoogleAnalyticsService;
use App\Traits\ValidatorTrait;


class GoogleAnalyticsController extends Controller
{
    private $service;

    use ValidatorTrait;

    function __construct()
    {
        $this->service = new GoogleAnalyticsService;
    }

    public function update(Request $request)
    {
       
        // 更新資料
        $create = $this->service->updateData($request);
        return $create;        
    }

    public function list()
    {
        // 取列表
        return $this->service->getlist();
    }

}
