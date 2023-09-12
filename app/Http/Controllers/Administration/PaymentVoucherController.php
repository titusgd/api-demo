<?php

namespace App\Http\Controllers\Administration;

use App\Services\Administrations\PaymentVoucherService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class PaymentVoucherController extends Controller
{
    public function add(Request $request)
    {
        // 驗證輸入資料
        $pv_service = new PaymentVoucherService();
        $valid_result = $pv_service->validatorAdd($request);
        if ($valid_result) return $valid_result;

        // 新增支憑、審核、通知
        $pv_service->addPaymentAndNotice(
            $request->title,
            $request->content,
            $request->product,
            $request->store,
            auth()->user()->id
        );

        // // execute service create data
        // $pv_data = $pv_service->createPaymentVoucher($request->content, $request->title, $request->store,auth()->user()->id);
        // $pvi_data = $pv_service->createPaymentVoucherItem($pv_data->id, $request->product);

        // $pv_review = $pv_service->createReview(
        //     // 如果總金額超過20000，則審核人員則為3層
        //     rank: $pv_service->getReviewRank('paymentVoucher', ($pvi_data['total'] >= 20000) ? 3 : 2),
        //     fk_id: $pv_data->id,
        //     type: 'paymentVoucher'
        // );

        // // -------------------- 新增 通知 ---------------------------------------
        // $reviews_user = $pv_service->selectReviewsUserList('paymentVoucher', $pv_data->id);
        // $notice = $pv_service->addNotice(
        //     "支付憑單 審核通知",
        //     "支付憑單 單號 " . $pv_service->getPaymentVoucherNumber($pv_data->id),
        //     [$reviews_user[0]['user_id']]
        // );

        return $pv_service->response('00', 'ok');
    }

    public function list(Request $request)
    {

        $pv_service = new PaymentVoucherService();

        // check input json
        $valid = $pv_service->validatorList($request);
        if ($valid) return $valid;

        return $pv_service->getList($request);
    }

    public function audit(Request $request)
    {
        $service = new PaymentVoucherService();
        // 1. 輸入資料檢查
        $valid = $service->validAudit($request);
        if ($valid) return $valid;

        // 2.變更狀態
        $result = $service->updateReview(
            'paymentVoucher',
            $request->id,
            $request->status,
            $request->reason
        );
        // 關閉通知
        $service->closeNoticeAndNoticeUser('paymentVoucher', $request->id, auth()->user()->id);

        // 檢查層級，並發布通知
        // 發通知給下一個審核者
        ($request->status == 'approval') && $review_list = $service->addAuditNotice('paymentVoucher', $request->id);

        // 審核未通過，發送通知給使用者
        ($request->status == 'fail') && $review_list = $service->addAuditNoticeFail('paymentVoucher', $request->id);

        return $service->response('00', "ok");
    }
}
