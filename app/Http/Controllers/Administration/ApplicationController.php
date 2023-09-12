<?php

namespace App\Http\Controllers\Administration;

use App\Models\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Administrations\ApplicationService;

class ApplicationController extends Controller
{
    public function add(Request $request)
    {
        $service = new ApplicationService();

        // 1. 資料驗證
        $valid = $service->validAdd($request);
        if ($valid) return $valid;

        // 2. 新增 伸議書
        $application = $service->createApplication($request->content, $request->title, $request->store);

        // 3. 新增 伸議書細項
        $application_item = $service->createApplicationItem($application->id, (array)$request->product);

        // 4. 新增審核人員清單
        $application_review = $service->createReview(
            rank: $service->getReviewRank('application', 3),
            fk_id: $application->id,
            type: 'application'
        );

        // 4.1 新增通知
        $reviews_user = $service->selectReviewsUserList('application', $application->id);

        $notice = $service->addNotice(
            "申議書 審核通知",
            "申議書 單號 " . $service->getApplicationNumber($application->id),
            [$reviews_user[0]['user_id']]
        );
        // 更新 通知資訊
        $service->updateNoticeLink($notice->id, env('APP_URL') . '/administration/proposal');    // 更新超連結
        $service->updateNoticeTypeAndFkId($notice->id, 'application', $application->id);                          // 更新 關聯
        $service->updateNoticeUserTypeAndFkId($notice->id, 'application', $application->id);                      // 更新關聯

        return $service->response('00', 'ok');
    }

    public function list(Request $request)
    {
        // 流程:
        // 1. request 資料驗證
        // 2. output response.

        $service = new ApplicationService($request);
        $valid = $service->validList($request);
        if ($valid) return $valid;

        return $service->getList($request);
    }

    public function delete(Request $request)
    {
        $service = new ApplicationService($request);
        $service->deleteApplicationAndReview($request->id);
        return $service->response('00', "ok");
    }

    public function audit(Request $request)
    {
        $service = new ApplicationService();
        // 1. 輸入資料檢查
        $valid = $service->validAudit($request);
        if ($valid) return $valid;
        // 2.變更狀態
        $result = $service->updateReview(
            'application',
            $request->id,
            $request->status,
            $request->reason
        );
        // 關閉通知
        $service->closeNoticeAndNoticeUser('application', $request->id, auth()->user()->id);
        // 檢查層級，並發布通知

        ($request->status == 'approval') && $review_list = $service->addAuditNotice('application', $request->id);

        ($request->status == 'fail') && $review_list = $service->addAuditNoticeResultFail($request->id);

        return $service->response('00', "ok");
    }
}
