<?php

namespace App\Services\Administrations;

use App\Services\Service;
use App\Services\Administrations\PaymentVoucherService;

use App\Models\Administration\Application;
use App\Models\Administration\ApplicationItem;

use App\Traits\ReviewTrait;
use App\Traits\DateTrait;
use App\Traits\NotifyTrait;

use Illuminate\Support\Facades\DB;

class ApplicationService extends Service
{
    use ReviewTrait;
    use DateTrait;
    use NotifyTrait;
    private $rules = [];
    private $err_msg = [];
    public $application, $review;

    public function validAdd(object $request)
    {
        $valid = Service::validatorAndResponse($request->all(), [
            "store" => 'required|integer|exists:stores,id',
            "title" => "required|string",
            "content" => "required| string",
            "product" => "required|array",
            "product.*.summary" => "required|string",
            "product.*.qty" => "required|integer",
            "product.*.price" => "required|numeric"
        ], [
            "store.required" => "01 store",
            "store.integer" => "01 store",
            "store.exists" => "02 store",
            "title.required" => "01 title",
            "title.string" => "01 title",
            "content.required" => "01 content",
            "content.string" => "01 content",
            "product.required" => "01 product",
            "product.array" => "01 product",
            "product.*.summary.required" => "01 summary",
            "product.*.summary.string" => "01 summary",
            "product.*.qty.required" => "01 qty",
            "product.*.qty.integer" => "01 qty",
            "product.*.price.required" => "01 price",
            "product.*.price.numeric" => "01 price"
        ]);
        if ($valid) return $valid;
    }
    public function validUpdate($request)
    {
        $valid = Service::validatorAndResponse($request->all(), $this->rules, $this->err_msg);
        if ($valid) return $valid;
    }

    function validList($request)
    {
        $valid = Service::validatorAndResponse($request->all(), [
            "store" => 'integer|exists:stores,id',
            "date.start" => "required|date",
            "date.end" => "required|date",
            "count" => 'required|integer',
            "page" => 'required|integer',

        ], [
            "store.required" => "01 store",
            "store.integer" => "01 store",
            "store.exists" => "02 store",
            "date.start.required" => '01 start',
            'date.start.date' => '01 start',
            "date.end.required" => '01 end',
            'date.end.date' => '01 end',
            'count.required' => '01 count',
            'count.integer' => '01 count',
            'page.required' => '01 page',
            'page.integer' => '01 page'
        ]);

        if ($valid) return $valid;
    }

    /** createApplication(array $data)
     *  @param array $data [ 'title' : string, 'content' : string]
     */
    public function createApplication(string $content, string $title, int $store_id): object
    {
        $application = new Application;
        $application->number = '';
        $application->user_id = auth()->user()->id;
        $application->store_id = $store_id;
        $application->title = $title;
        $application->content = $content;
        $application->save();

        $application->number = $this->createNumber($application->id);
        $application->save();

        $this->application = $application;
        return $application;
    }

    function createApplicationItem(int|string $application_id, array $item)
    {
        foreach ($item as $value) {
            $application_item = new ApplicationItem;
            $application_item->summary = $value['summary'];
            $application_item->qty = $value['qty'];
            $application_item->price = $value['price'];
            $application_item->application_id = $application_id;
            $application_item->save();
        }
    }

    public function updateApplication(array $data, $where)
    {
        $query = new Application;
        foreach ($where as $key => $val) {
            $query = $query->where($val[0], $val[1], $val[2]);
        }
        $update = $query->update($data);
        return $update;
    }

    function getList($request)
    {
        $application = $this->selectApplication(
            $request->store,
            [
                'start' => $request['date']['start'],
                'end' => $request['date']['end']
            ],
            $request->count
        );
        $output = [];
        $output['page'] = [];
        $output['data'] = [];
        $setPage = function ($application) use (&$output) {
            $output['page']['total'] = $application['last_page'];
            $output['page']['countTotal'] = $application['total'];
            $output['page']['page'] = $application['current_page'];
        };

        foreach ($application['data'] as $key => $val) {

            $output['data'][$key]['id'] = $val['id'];
            $output['data'][$key]['number'] = $val['number'];
            $output['data'][$key]['date'] = self::dateFormat((string)$val['createdAt']);
            $output['data'][$key]['applicant'] = $val['applicant'];
            $output['data'][$key]['title'] = $val['title'];
            $output['data'][$key]['content'] = $val['content'];
            $output['data'][$key]['status'] = [];
            $output['data'][$key]['product'] = [];

            // reviews 
            foreach ($val['reviews'] as $review_key => $review_val) {
                $output['data'][$key]['status'][$review_key]['id'] = $review_val['user_id'];
                $output['data'][$key]['status'][$review_key]['name'] = $review_val['user_name'];
                $output['data'][$key]['status'][$review_key]['rank'] = $review_val['rank'];
                $output['data'][$key]['status'][$review_key]['audit'] = $this->numberToStatus($review_val['status']);
                $output['data'][$key]['status'][$review_key]['reason'] = $review_val['note'];
                $output['data'][$key]['status'][$review_key]['date'] =
                    (!empty($review_val['date'])) ? self::dateFormat($review_val['date']) : [];
            }
            // application_items
            foreach ($val['application_items'] as $a_key => $a_val) {
                $output['data'][$key]['product'][$a_key]['id'] = $a_val['id'];
                $output['data'][$key]['product'][$a_key]['summary'] = $a_val['summary'];
                $output['data'][$key]['product'][$a_key]['qty'] = (int)$a_val['qty'];
                $output['data'][$key]['product'][$a_key]['price'] = floatval($a_val['price']);
            }
        }
        $setPage($application);
        return Service::response_paginate('00', 'ok', $output['data'], $output['page']);
    }

    function res()
    {
        if ($this->application and $this->review) return Service::response('00', 'ok');
    }

    function deleteApplicationAndReview(int $application_id)
    {
        $this->deleteApplication('id', $application_id);
        $this->deleteReview('fk_id', $application_id);
    }

    function deleteApplication(string $column, int|string $value)
    {
        $del = Application::where($column, '=', $value)->delete();
    }

    function deleteReview(string $column, int|string $value)
    {
        $del = $this->getReviewModel->where($column, '=', $value)->delete();
    }

    function checkApplicationId(int $id): bool
    {
        $application = Application::where('id', '=', $id)->first();
        return ($application) ? (bool)true : (bool)false;
    }

    function createNumber($application_id)
    {
        $title = "PP";
        // 8碼
        $code = str_pad($application_id, 8, '0', STR_PAD_LEFT);
        return $code;
    }

    function selectApplication(int $store_id, array $date, int $count)
    {
        $application_data = Application::query()->select(
            'id',
            'number',
            'store_id',
            'title',
            'content',
            'created_at as createdAt',
            DB::raw('(select `name` from `users` where users.id = applications.user_id) as applicant')
        )
            // 關聯查詢 
            ->with([
                'application_items:application_id,id,summary,qty,price',
                'reviews' => function ($query) {
                    // 多條件查詢
                    $query->select(
                        'id',
                        'type',
                        'fk_id',
                        'rank',
                        'date',
                        'status',
                        'user_id',
                        'note',
                        DB::raw('(select `name` from `users` where id = reviews.user_id )as user_name')
                    )->where('type', '=', 'application')->orderBy('rank', 'desc');
                }
            ])
            ->where('store_id', '=', $store_id)
            ->where('created_at', '>', $date['start'] . ' 00:00:00')
            ->where('created_at', '<', $date['end'] . ' 23:59:59')
            ->orderBy('id', 'DESC')
            ->paginate($count)
            ->toArray();
        return $application_data;
    }

    public function validAudit($request)
    {
        $rules = [
            'id' => 'required|integer|exists:applications,id',
            'status' => 'required|string',
        ];

        (!empty($request->reason)) && $rules['reason'] = 'required|string';

        $valid = Service::validatorAndResponse($request->all(), $rules, [
            "id.required" => "01 id",
            'id.integer' => '01 id',
            'id.exists' => '02 id',
            'status.required' => '01 status',
            'status.string' => '01 status',
            'reason.required' => '01 reason',
            'reason.string' => '01 reason'
        ]);

        if ($valid) return $valid;
        if ($request->status != 'approval' && $request->status != 'fail') return Service::response('01', 'status');
    }

    public function getApplicationNumber($application_id)
    {
        $application_data = Application::find($application_id);
        return $application_data->number;
    }

    // 新增通知
    public function addAuditNotice($type, $fk_id)
    {
        $review_list = self::selectReviewsUserList($type, $fk_id);
        $review = self::selectReviewRank($type, $fk_id, auth()->user()->id);

        // 新增通知
        $add_notice = function ($type, $number, $user_id, $fk_id) {

            $notice = self::addNotice(
                (($type == 'application') ? "申議書" : "支付憑單") . " 審核通知",
                (($type == 'application') ? "申議書" : "系統新增支付憑單") . " 單號 " . $number,
                $user_id,
                false
            );
            // env('APP_URL').'
            $update_notice_link = $this->updateNoticeLink($notice->id, env('APP_URL') . '/administration/proposal');
            $update_notice_type = self::updateNoticeTypeAndFkId($notice->id, $type, $fk_id);
            $add_notice_user = self::updateNoticeUserTypeAndFkId($notice->id, $type, $fk_id);
        };
        // 如果有下個層級，則發送通知下個層級
        if (($review['rank'] - 1) != 0) {
            $notice_user = self::getReviewModel()
                ->select('user_id', 'rank')
                ->where('type', '=', $type)
                ->where('fk_id', '=', $fk_id)
                ->where('rank', '=', ($review['rank'] - 1))
                ->first();

            $add_notice(
                'application',
                self::getApplicationNumber($fk_id),
                [$notice_user->user_id],
                $fk_id
            );
        }

        // 如果為最後一個層級，則新建支出憑單，並發送通知
        if (($review['rank'] - 1) == 0) {
            // 取得申議書資料
            $application_data = Application::select('*')->where('id', '=', $fk_id)->get();
            $application_item_data = ApplicationItem::select('summary', 'qty', 'price')
                ->where('application_id', '=', $fk_id)
                ->get()
                ->toArray();

            // --------------------新增支憑--------------------------------
            $pv_service = new PaymentVoucherService();
            // 新增主表
            $pv_data = $pv_service->createPaymentVoucher(
                $application_data['0']->content,
                $application_data['0']->title,
                $application_data['0']->store_id,
                $application_data['0']->user_id
            );
            // 新增細項
            $pvi_data = $pv_service->createPaymentVoucherItem(
                $pv_data->id,
                $application_item_data
            );

            // --------------------通知---------------------------------
            // 建立 審核人員清單
            $application_review = self::createReview(
                rank: self::getReviewRank('application', 3),    // 使用與申議書相同審核人員
                fk_id: $pv_data->id,
                type: 'paymentVoucher'
            );

            // 4 新增通知
            $reviews_user = self::selectReviewsUserList('paymentVoucher', $pv_data->id);

            $add_notice(
                'paymentVoucher',
                $pv_service->getPaymentVoucherNumber($pv_data->id),
                [$reviews_user[0]['user_id']],
                $pv_data->id
            );
        }
    }

    public function addAuditNoticeResultFail($fk_id)
    {
        $app_data = Application::select('id', 'number', 'user_id')->first();
        // 取得review資訊
        $review_data = self::getReviewModel()->select('id', 'type', 'fk_id', 'note')
            ->where('type', '=', 'application')
            ->where('user_id', '=', auth()->user()->id)
            ->where('fk_id', '=', $fk_id)->first();

        $notice = self::addNotice(
            '申議書審核通知',
            " 單號 " . $app_data->number . '
            審核結果：未核准
            備註：' . $review_data->note,
            [$app_data->user_id]
        );

        // 更新 通知連結
        $notice_link = self::updateNoticeLink($notice->id, env('APP_URL') . '/administration/proposal');
        // 更新 通知 關聯
        $add_notice_user = self::updateNoticeUserTypeAndFkId($notice->id, 'application', $fk_id);
    }
}
