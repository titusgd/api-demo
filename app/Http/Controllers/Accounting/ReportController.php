<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Accounting\ReportService;

class ReportController extends Controller
{    
    public function import_record(Request $request)
    {
        $service = new ReportService($request);
        return $service->create($request->all());
    }

    public function transaction_record(Request $request)
    {
        // 在 request 新增一個 page 參數
        // $request->request->add(['page' => $request->input("sn")]);
        $service = new ReportService($request);
        $req = $request->all();

        // 檢查輸入資料
        $vali = $service->transactioValidator($req);
        $vali_time = $service->transactioValidatorTime($req);
        
        // 回傳錯誤訊息
        if ($vali) return $vali;
        if ($vali_time) return $vali_time;

        // 回傳列表
        return $service->getList($req);
    }
}
