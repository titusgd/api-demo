<?php

namespace App\Services\Accounts;

use App\Models\Account\Notice;
use App\Models\Account\NoticeResponse;
use App\Models\Account\NoticeUser;
use App\Services\Service;
use Illuminate\Support\Facades\DB;
use App\Traits\DateTrait;
use App\Traits\NotifyTrait;

class NoticeService extends Service
{
    use DateTrait;
    use NotifyTrait;
    function validateAdd($request)
    {
        $valid = Service::validatorAndResponse($request->all(), [
            'title' => 'required|string|max:200',
            'content' => 'required|string|max:500',
            'recipient' => 'required|array',
            'recipient.*' => 'integer'
        ], [
            'title.required' => '01 title',
            'title.string' => '01 title',
            'title.max' => '01 title',
            'content.required' => '01 content',
            'content.string' => '01 content',
            'content.max' => '01 content',
            'recipient.required' => '01 recipient',
            'recipient.array' => '01 recipient',
            'recipient.*.integer' => '01 recipient',
        ]);

        if ($valid) return $valid;
    }

    function validateList($request)
    {
        $rules = [];
        // XXX: 過多判斷，未來可否與...協商減少無意義傳送。
        (!empty($request->close)) && $rules['close'] = 'required|boolean';
        (!empty($request->count)) && $rules['count'] = 'required|integer';
        (!empty($request->page)) && $rules['page'] = 'required|integer';

        $valid = Service::validatorAndResponse(
            $request->all(),
            $rules,
            [
                'close.required' => '01 close',
                'close.boolean' => '01 close',
                'count.required' => '01 count',
                'count.integer' => '01 count',
                'page.required' => '01 page',
                'page.integer' => '01 page',
                'type.required' => '01 type',
                'type.string' => '01 type'
            ]
        );

        if ($valid) return $valid;
    }

    function validateRecipient($request)
    {
        // XXX: 目前為檢測到一條錯誤即回傳錯誤，容易造成過多請求，增加server負擔，
        // 未來可否變更為一次輸出錯誤陣列 or 物件，已減少請求次數，增加效能。
        $valid = Service::validatorAndResponse($request->all(), [
            'id' => 'required|integer|exists:notices,id',
            'content' => 'required|string|max:500',
        ], [
            'id.required' => '01 id',
            'id.integer' => '01 id',
            'id.exists' => '02 id',
            'content.required' => '01 content',
            'content.string' => '01 content',
            'content.max' => '07 content'
        ]);
        if ($valid) return $valid;
        // 操作權限 如果 notices.close_type true 則不可進行留言
        $close_type = (bool)$this->getNoticeCloseType($request->id);
        if ($close_type) return Service::response('06', 'close');

        // 是否是與會人員
        $check_user = $this->checkUserReplyOperate($request->id, auth()->user()->id);
        if (!$check_user) return Service::response('06', 'close');
    }
    public function validBatchClose($request)
    {
        // TODO:依據前端提供的json格是做檢查調整
        $rules = [];
        $error_massage = [];
        $valid = Service::validatorAndResponse($request->all(), $rules, $error_massage);
        if ($valid) return $valid;
    }

    public function validBatchCloseSelf($request)
    {
        // TODO:依據前端提供的json格是做檢查調整
        $rules = [];
        $error_massage = [];
        $valid = Service::validatorAndResponse($request->all(), $rules, $error_massage);
        if ($valid) return $valid;
    }

    function validateCaseType($request)
    {
        $valid = Service::validatorAndResponse($request->all(), [
            "id" => 'required|integer|exists:notices,id'
        ], [
            'id.required' => '01 id',
            'id.integer' => '01 id',
            'id.exists' => '02 id'
        ]);

        if ($valid) return $valid;
    }


    function validForward($request)
    {
        $valid = Service::validatorAndResponse(
            $request->all(),
            [
                'id' => 'required|integer|exists:notices,id',
                'recipient' => 'required|array',
                'recipient.*' => 'required|integer|exists:users,id'
            ],
            [
                'id.required' => '01 id',
                'id.integer' => '01 id',
                'id.exists' => '02 id',
                'recipient.required' => '01 recipient',
                'recipient.array' => '01 recipient',
                'recipient.*.required' => '01 recipient',
                'recipient.*.integer' => '01 recipient',
                'recipient.*.exists' => '02 recipient'
            ]
        );
        if ($valid) return $valid;
        $notice_users = $this->selectNoticeUserWhereInId($request->recipient, $request->id)->toArray();
        if ($notice_users) return Service::response('03', 'recipient');
    }

    public function addForward($notice_id, $recipient_array)
    {
        $insert_data = [];
        $date = new \DateTime();
        foreach ($recipient_array as $key => $val) {
            $insert_data[$key]['notice_id'] = $notice_id;
            $insert_data[$key]['forwarder_id'] = auth()->user()->id;
            $insert_data[$key]['recipient_id'] = $val;
            $insert_data[$key]['close_type'] = false;
            $insert_data[$key]['created_at'] = $date;
            $insert_data[$key]['updated_at'] = $date;
        }
        $notice_users = NoticeUser::insert($insert_data);
    }

    public function addNoticeResponse(int $notice_id, string $content)
    {
        $this->createNoticeRecipient($notice_id, $content);
    }

    public function createNoticeRecipient(int $notice_id, string $content)
    {
        $notice_response = new NoticeResponse;
        $notice_response->notice_id = $notice_id;
        $notice_response->user_id = auth()->user()->id;
        $notice_response->content = $content;
        $notice_response->save();
    }

    public function getNoticeCloseType(int $notice_id): bool
    {
        $notice = Notice::select('close_type')->where('id', '=', $notice_id)->first();
        return $notice->close_type;
    }

    public function checkUserReplyOperate($notice_id, $user_id)
    {
        $notice_data = Notice::select('id')->where('id', '=', $notice_id)->where('user_id', '=', $user_id)->first();
        if ($notice_data) return true;
        $notice_users = NoticeUser::select('id')->where('notice_id', $notice_id)->where('recipient_id', '=', $user_id)->first();
        if ($notice_users) return true;
        return false;
    }

    public function updateNoticeCloseType($notice_id, $type)
    {
        $notice_data = Notice::find($notice_id, 'id');
        $notice_data->close_type = $type;
        $notice_data->save();
        ($type == 'true' || $type == true) && DB::update(DB::raw("UPDATE notice_users SET close_type = 1 WHERE notice_id = {$notice_id}"));
    }

    public function updateNoticeUserCloseType($notice_id, $type)
    {
        $notice_user = NoticeUser::where('notice_id', '=', $notice_id)
            ->where('recipient_id', '=', auth()->user()->id)
            ->update([
                'close_type' => $type
            ]);
    }

    public function getList($type, $page, $count)
    {
        $notice_id = $this->selectNoticeId(auth()->user()->id, $type);
        $notice_result = $this->selectNoticeData(
            notice_id: $notice_id,
            type: $type,
            count: $count,
            page: $page
        );

        $page = [];
        if ((!empty($page)) || (!empty($count))) {
            $page['total'] = $notice_result['last_page'];
            $page['countTotal'] = $notice_result['total'];
            $page['page'] = $notice_result['current_page'];
        }

        $data = [];
        $notice_data = (!empty($page)) || (!empty($count)) ? $notice_result['data'] : $notice_result;

        // 資料格式化
        foreach ($notice_data as $key => $val) {
            $data[$key]['id'] = $val['id'];
            $data[$key]['date'] = $this->dateFormat($val['date']);
            $data[$key]['sender']['id'] = $val['sender_id'];
            $data[$key]['sender']['name'] = $val['sender_name'];

            $data[$key]['sender']['close'] = (bool)$val['close'];

            // 收件者 
            $data[$key]['recipient'] = [];
            foreach ($val['notice_user'] as $key2 => $val2) {
                if ($val['sender_id'] == $val2['user_id']) continue;
                $temp_arr = [];
                $temp_arr['id'] = $val2['user_id'];
                $temp_arr['name'] = $val2['user_name'];
                $temp_arr['close'] = (bool)$val2['close'];
                // 無轉發者時，寫入null
                // 有轉發者寫入 ['id'=>value,'name'=>value]
                $temp_arr['forwarder'] =
                    (!empty($val2['forwarder_id']) & $val2['forwarder_id'] != null) ?
                    ['id' => $val2['forwarder_id'], 'name' => $val2['forwarder_name']] :
                    null;
                array_push($data[$key]['recipient'], $temp_arr);
            }

            $data[$key]['title'] = $val['title'];
            $data[$key]['content'] = $val['content'];

            // 回覆
            $data[$key]['response'] = [];
            foreach ($val['response'] as $key2 => $val2) {
                $data[$key]['response'][$key2]['responder']['id'] = $val2['user_id'];
                $data[$key]['response'][$key2]['responder']['name'] = $val2['user_name'];
                $data[$key]['response'][$key2]['date'] = $this->dateFormat($val2['date']);
                $data[$key]['response'][$key2]['content'] = $val2['content'];
            }
            $data[$key]['link'] = (!empty($val['link'])) ? $val['link'] : '';
        }
        return ((!empty($page)) || (!empty($count))) ?
            Service::response_paginate('00', 'ok', $data, $page) :
            Service::response('00', 'ok', $data);
    }

    public function selectNoticeId($user_id, $close_type)
    {
        $notice_data = Notice::select('id')
            ->where('user_id', '=', $user_id)
            ->where('close_type', '=', $close_type)
            ->get();
        $notice_user = NoticeUser::select('notice_id')
            ->where('recipient_id', '=', $user_id)
            ->where('close_type', '=', $close_type)
            ->get();

        $notice_data = array_column($notice_data->toArray(), 'id');
        $notice_user = array_column($notice_user->toArray(), 'notice_id');
        return array_merge($notice_data, $notice_user);
    }

    public function selectNoticeData($notice_id, $count = null, $page = null, $type)
    {
        // XXX: 未來可在優化查詢，已增效能。
        $notice = Notice::query()
            ->select(
                'id',
                'created_at as date',
                'user_id as sender_id',
                DB::raw('(select name from users where users.id = notices.user_id)as sender_name'),
                'close_type as close',
                'title',
                'content',
                'link'
            )
            ->with([
                // notice_users 查詢
                'notice_user' => function ($query) {
                    $query->select(
                        'id',
                        'notice_id',
                        'recipient_id as user_id',
                        DB::raw('(select name from users where id = notice_users.recipient_id) as user_name'),
                        'close_type as close',
                        'forwarder_id',
                        DB::raw('(select name from users where users.id = notice_users.forwarder_id) as forwarder_name')

                    )->orderBy('created_at', 'desc');
                },
                // notice_responses 查詢
                'response' => function ($query) {
                    $query->select(
                        'notice_id',
                        'content',
                        'user_id',
                        'created_at as date',
                        // 目前預設 users.code+users.name ，output "000000-XXX"
                        // DB::raw('(select CONCAT(`code`,\'-\',`name`) from `users` where users.id = notice_responses.user_id)as user_name')
                        DB::raw('(select name from `users` where users.id = notice_responses.user_id)as user_name')
                    )->orderBy('created_at', 'desc');
                },
            ])
            ->whereIn('id', $notice_id);

        (!empty($type)) && $notice = $notice->where('close_type', '=', $type);

        // 主表結果依照日期建立前後排序
        $notice = $notice->orderBy('created_at', 'desc');

        // 如果count為null ，則使用get()查詢結果，如果不為null 則使用 paginate 查詢結果
        $notice = (!empty($count)) ? $notice->paginate($count)->toArray() : $notice->get()->toArray();

        return $notice;
    }

    public function selectNoticeUserId(int $notice_id): object
    {
        $notice = Notice::find($notice_id);
        return $notice;
    }

    /** batchClose
     *  匹次關閉通知(主表)
     */
    public function batchClose(array $notice_id)
    {
        Notice::whereIn('id', $notice_id)->update(['close_type' => 1]);
    }
    /** batchCloseSelf
     *  批次關閉個人通知
     */
    public function batchCloseSelf(int $user_id)
    {
        $notice_user = NoticeUser::where('recipient_id', '=', $user_id)
            ->update(['close_type' => 1]);
    }
}
