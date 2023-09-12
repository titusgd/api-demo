<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Accounting\PettyCashService;

class PettyCashController extends Controller
{

    // 零用金變更狀態
    public function update(Request $request)
    {
        $service = new PettyCashService($request);
        // 格式檢查，並回傳錯誤
        if ($service->validate()) return $service->validate();

        // 更新
        $update_status = $service->updatePettyCashList();
        return ($update_status === true) ? $service->response("00", "ok") : $service->response("999", "");
    }

    // 零用金 申請紀錄
    public function apply(Request $request)
    {
        $service = new PettyCashService($request);
        $vali = $service->validateApply();
        if ($vali) return $vali;

        $apply_data = $service->applyData();
        return $apply_data;
    }
    // 零用金 交易明細
    public function detail(Request $request)
    {
        $service = new PettyCashService($request);
        $vali = $service->validateDetail();
        if ($vali) return $vali;

        return $service->list();
    }
}
