<?php

namespace App\Http\Controllers\Account;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Accounts\NoticeService;

class NoticeController extends Controller
{
    // 發送
    public function add(Request $request)
    {
        $service = new NoticeService($request);
        $valid = $service->validateAdd($request);
        if ($valid) return $valid;

        $notice = $service->addNotice(
            $request->title,        // 通知標題
            $request->content,      // 通知內容
            $request->recipient     // 與會人員
        );

        return $service->response('00', 'ok');
    }

    // 列表
    public function list(Request $request)
    {
        $service = new NoticeService($request);

        $valid = $service->validateList($request);
        if ($valid) return $valid;
        $list = $service->getList($request->close, $request->page, $request->count);
        return  $list;
    }

    // 回覆
    public function reply(Request $request)
    {
        $service = new NoticeService;

        $valid = $service->validateRecipient($request);
        if ($valid) return $valid;
        $add_reply = $service->addNoticeResponse($request->id, $request->content);
        return $service->response('00', 'ok');
    }

    // 結案
    public function close(Request $request)
    {
        $service = new NoticeService;

        $valid = $service->validateCaseType($request);
        if ($valid) return $valid;
        // 查詢是否是發起人
        $notice_data = $service->selectNoticeUserId($request->id);
        if ($notice_data->user_id == auth()->user()->id) {
            // true:結案 false:未結案
            $updateNotice = $service->updateNoticeCloseType($request->id, true);
        }
        $service->updateNoticeUserCloseType($request->id, true);

        return $service->response('00', 'ok');
    }

    // 轉發
    public function forward(Request $request)
    {
        $service = new NoticeService;
        $forward = $service->validForward($request);
        if ($forward) return $forward;
        // 檢查主表是否已經關閉，如果關閉則不可轉發。
        $notice = $service->selectNoticeUserId($request->id);
        if($notice->close_type == true) return $service->response('06', 'id');

        $add_forward = $service->addForward($request->id, $request->recipient);
        return $service->response('00', 'ok');
    }
    // 主表
    public function batchClose(Request $request){
        $service = new NoticeService();
        $valid = $service->validBatchClose($request);
        if ($valid) return $valid;
        $notice_ids = [];
        
        $service->batchClose($notice_ids);
        return $service->response('00','ok');
    }
    // 個人
    public function batchCloseSelf(Request $request){
        $service = new NoticeService();
        $service->batchCloseSelf(auth()->user()->id);
        return $service->response('00','ok');
    }
}
