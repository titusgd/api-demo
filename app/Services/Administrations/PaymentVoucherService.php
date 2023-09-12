<?php

namespace App\Services\Administrations;

use App\Services\Service;
use App\Models\Administration\PaymentVoucher;
use App\Models\Administration\PaymentVoucherItem;
use App\Models\store;
use App\Traits\ReviewTrait;
use App\Traits\NotifyTrait;
use Illuminate\Support\Facades\DB;
use App\Traits\DateTrait;

class PaymentVoucherService extends Service
{
    use ReviewTrait, NotifyTrait;
    use DateTrait;

    private $notice_title = '支付憑單 審核通知';
    private $notice_content = '支付憑單 單號 ';
    private $review_type = 'paymentVoucher';
    private $validation_error_msg = [
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
        "product.*.price.numeric" => "01 price",
        "date.start.required" => '01 start',
        'date.start.date' => '01 start',
        "date.end.required" => '01 end',
        'date.end.date' => '01 end',
        'count.required' => '01 count',
        'count.integer' => '01 count',
        'page.required' => '01 page',
        'page.integer' => '01 page'
    ];

    public function validatorAdd($request)
    {
        $valid = Service::validatorAndResponse(
            $request->all(),
            [
                "store" => 'required|integer|exists:stores,id',
                "title" => "required|string",
                "content" => "required|string",
                "product" => "required|array",
                "product.*.summary" => "required|string",
                "product.*.qty" => "required|integer",
                "product.*.price" => "required|numeric"
            ],
            $this->validation_error_msg
            // [
            //     "store.required" => "01 store",
            //     "store.integer" => "01 store",
            //     "store.exists" => "02 store",
            //     "title.required" => "01 title",
            //     "title.string" => "01 title",
            //     "content.required" => "01 content",
            //     "content.string" => "01 content",
            //     "product.required" => "01 product",
            //     "product.array" => "01 product",
            //     "product.*.summary.required" => "01 summary",
            //     "product.*.summary.string" => "01 summary",
            //     "product.*.qty.required" => "01 qty",
            //     "product.*.qty.integer" => "01 qty",
            //     "product.*.price.required" => "01 price",
            //     "product.*.price.numeric" => "01 price",
            // ]
        );
        if ($valid) return $valid;
    }

    function validatorList($request)
    {
        $valid = Service::validatorAndResponse(
            $request->all(),
            [
                "store" => 'required|integer|exists:stores,id',
                "date.start" => "required|date",
                "date.end" => "required|date",
                "count" => 'required|integer',
                "page" => 'required|integer',

            ],
            $this->validation_error_msg
            // [
            //     "store.required" => "01 store",
            //     "store.integer" => "01 store",
            //     "store.exists" => "02 store",
            //     "date.start.required" => '01 start',
            //     'date.start.date' => '01 start',
            //     "date.end.required" => '01 end',
            //     'date.end.date' => '01 end',
            //     'count.required' => '01 count',
            //     'count.integer' => '01 count',
            //     'page.required' => '01 page',
            //     'page.integer' => '01 page'
            // ]
        );

        if ($valid) return $valid;
    }


    public function createPaymentVoucher(string $content, string $title, int $store, $user_id): object
    {
        $pv = new PaymentVoucher;
        $pv->store_id = $store;
        $pv->title = $title;
        $pv->content = $content;
        $pv->user_id = $user_id;
        $pv->number = '';
        $pv->save();
        $pv->number = $this->createNumber($pv->id);
        $pv->save();
        return $pv;
    }
    /** createPaymentVoucherItem()
     *  @return array ['id'_list'=>[value1,value2...],'total'=>value]
     */
    public function createPaymentVoucherItem(int $payment_voucher_item_id, array $data): array
    {
        $pvi_id_list = [];
        // 自動加總price
        $total = 0;
        foreach ($data as $index => $item) {
            $pvi = new PaymentVoucherItem;
            $pvi->summary = $item['summary'];
            $pvi->qty = $item['qty'];
            $pvi->price = $item['price'];
            $pvi->payment_voucher_id = $payment_voucher_item_id;
            $pvi->save();

            array_push($pvi_id_list, $pvi->id);
            // price sum 
            $total += $item['price'];
        }
        return ['id_list' => $pvi_id_list, 'total' => $total];
    }

    private function createNumber(int $number): string
    {
        $title = '';
        $code = str_pad($number, 8, '0', STR_PAD_LEFT);
        return $title . $code;
    }

    public function getList(object $request): object
    {
        // get data
        $payment = $this->selectPayment(
            $request->store,
            [
                'start' => $request['date']['start'],
                'end' => $request['date']['end']
            ],
            $request->count
        );
        // init output 
        $output = [];
        $output['page'] = [];
        $output['data'] = [];
        // page Information setting
        $output['page']['total'] = $payment['last_page'];
        $output['page']['countTotal'] = $payment['total'];
        $output['page']['page'] = $payment['current_page'];

        // setting output data
        foreach ($payment['data'] as $key => $val) {

            $output['data'][$key]['id'] = $val['id'];
            $output['data'][$key]['number'] = $val['number'];
            $output['data'][$key]['date'] = self::dateFormat($val['createdAt']);
            $output['data'][$key]['applicant'] = $val['create_user_name'];
            $output['data'][$key]['title'] = $val['title'];
            $output['data'][$key]['content'] = $val['content'];
            $output['data'][$key]['status'] = [];
            $output['data'][$key]['product'] = [];
            // reviews 
            foreach ($val['reviews'] as $review_key => $review_val) {
                $output['data'][$key]['status'][$review_key]['id'] = $review_val['rank_user_id'];
                $output['data'][$key]['status'][$review_key]['name'] = $review_val['user_name'];
                
                // 狀態轉換 
                $output['data'][$key]['status'][$review_key]['audit'] = $this->numberToStatus($review_val['status']);
                $output['data'][$key]['status'][$review_key]['reason'] = $review_val['reason'];
                $output['data'][$key]['status'][$review_key]['date'] =
                    (!empty($review_val['date'])) ? self::dateFormat($review_val['date']) : [];
            }
            // payment_voucher_items
            foreach ($val['payment_voucher_items'] as $a_key => $a_val) {
                $output['data'][$key]['product'][$a_key]['id'] = $a_val['id'];
                $output['data'][$key]['product'][$a_key]['summary'] = $a_val['summary'];
                $output['data'][$key]['product'][$a_key]['qty'] = (int)$a_val['qty'];
                $output['data'][$key]['product'][$a_key]['price'] = (float)$a_val['price'];
            }
        }
        // return $output;
        return Service::response_paginate('00', 'ok', $output['data'], $output['page']);
    }

    public function selectPayment(int $store_id, array $date, int $count): array
    {
        $payment = PaymentVoucher::query()
            //  payment_voucher column
            ->select(
                'id',
                'number',
                'store_id',
                'title',
                'content',
                DB::raw('created_at as createdAt'),
                DB::raw('(select `name` from `users` where users.id = payment_vouchers.user_id)as create_user_name')
            )->with([
                'payment_voucher_items:payment_voucher_id,id,summary,qty,price',
                // reviews column and where
                'reviews' => function ($query) {
                    // 多條件查詢
                    $query->select(
                        'id',
                        'type',
                        'fk_id',
                        'rank',
                        'date',
                        'status',
                        'note as reason',
                        'user_id as rank_user_id',
                        DB::raw('(select `name` from `users` where id = reviews.user_id )as user_name')
                    )->where('type', '=', $this->review_type)->orderBy('rank', 'desc');
                }
            ])
            // payment_vouchers where
            ->where('store_id', '=', $store_id)
            ->where('created_at', '>', $date['start'] . ' 00:00:00')
            ->where('created_at', '<', $date['end'] . ' 23:59:59')
            ->orderBy('id', 'DESC')
            ->paginate($count)->toArray();

        return $payment;
    }

    public function getPaymentVoucherNumber($payment_voucher_id)
    {
        $payment_voucher = PaymentVoucher::find($payment_voucher_id);
        return $payment_voucher->number;
    }

    public function validAudit($request)
    {
        $rules = [
            'id' => 'required|integer|exists:payment_vouchers,id',
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
        // 檢查是否可以審核
        // 檢查是否有不予通過的
        $review = $this->reviewQuery($request->id)
            ->where('status', '=', '2')
            ->first();
        if ($review) return Service::response('207', 'id');

        // 檢查是否已經審核過
        $review = $this->reviewQuery($request->id)
            ->where('user_id', '=', auth()->user()->id)
            ->first();
        if ($review->status != '1') return Service::response('202', 'id');

        // 檢查前一級，是否審核通過
        $review2 = $this->reviewQuery($request->id)
            ->where('rank', '=', ($review['rank'] + 1))
            ->first();
        if ($review2) {
            if ($review2->status != '3') return Service::response('208', 'id');
        }
    }

    public function reviewQuery($fk_id, $select = ['*'])
    {
        return self::getReviewModel()->select($select)
            ->where('type', '=', 'paymentVoucher')
            ->where('fk_id', '=', $fk_id);
    }

    public function addAuditNotice(string $type,int $fk_id)
    {
        // default setting
        $notice_uri = env('APP_URL') . '/administration/disbursementVoucher';
        
        $review = self::selectReviewRank($type, $fk_id, auth()->user()->id);
        // 如果有下個層級，則發送通知下個層級
        if (($review['rank'] - 1) != 0) {
            $notice_user = self::getReviewModel()
                ->select('user_id', 'rank')
                ->where('type', '=', $type)
                ->where('fk_id', '=', $fk_id)
                ->where('rank', '=', ($review['rank'] - 1))
                ->first();
            // ------------------------- 通知 ----------------------------------
            $notice = self::createNotice(
                $this->notice_title,
                $this->notice_content . self::getPaymentVoucherNumber($fk_id)
            );

            $notice_user = self::createNoticeUser($notice->id, [$notice_user->user_id]);
            $this->updateNoticeData($notice->id, $type, $fk_id, $notice_uri);
        }

        // 如果沒有下一個審核人員，則結束流程，審核結果返還給建立者
        if (($review['rank'] - 1) == 0) {
            $payment_voucher = PaymentVoucher::select('title', 'content', 'user_id', 'number')->where('id', '=', $fk_id)
                ->first();

            // 新增通知 
            $add_notice = self::addNotice(
                // "支付憑單 審核通知",
                '支付憑單 審核結果通知',
                $this->notice_content . ':' . $payment_voucher->number . '
                審核結果 : ' . $this->num2StatusCH($review['status']),
                [$payment_voucher->user_id]
            );
            $this->updateNoticeData($add_notice->id, $type, $fk_id, $notice_uri);
        }
    }

    public function getStoreName(int $store_id)
    {
        return store::select('store')->where('id', '=', $store_id)->first();
    }

    public function addPaymentAndNotice(string $title,string $content, $product, $store_id, $user_id)
    {
        // 新增支憑
        $pv_data = $this->createPaymentVoucher($content, $title, $store_id, $user_id);
        $pvi_data = $this->createPaymentVoucherItem($pv_data->id, $product);

        // 新增審核人員
        $this->createReview(
            rank: $this->getReviewRank($this->review_type, ($pvi_data['total'] >= 20000) ? 3 : 2),
            fk_id: $pv_data->id,
            type: $this->review_type
        );
        $reviews_user = $this->selectReviewsUserList($this->review_type, $pv_data->id);

        // 新增 審核 通知
        $add_notice = $this->addNotice(
            // "支付憑單 審核通知",
            $this->notice_title,
            $this->notice_content . $this->getPaymentVoucherNumber($pv_data->id),
            [$reviews_user[0]['user_id']]
        );
        $this->updateNoticeData($add_notice->id, 'paymentVoucher', $pv_data->id, env('APP_URL') . '/administration/disbursementVoucher');
    }

    public function addAuditNoticeFail(string $type,int $fk_id)
    {
        // 查詢審核、支憑
        $review = self::selectReviewRank($type, $fk_id, auth()->user()->id);
        $payment_voucher = PaymentVoucher::select('title', 'content', 'user_id', 'number')
            ->where('id', '=', $fk_id)
            ->first();

        $add_notice = self::addNotice(
            // "支付憑單 審核通知",
            '支付憑單 審核結果通知',
            $this->notice_content . ':' . $payment_voucher->number . '
            審核結果 : ' . $this->num2StatusCH($review['status']),
            [$payment_voucher->user_id]
        );
        $this->updateNoticeData($add_notice->id, $type, $fk_id, env('APP_URL') . '/administration/disbursementVoucher');
    }

    /** num2StatusCH()
     *  代號轉狀態中文
     */
    public function num2StatusCH(int $number)
    {
        switch ($number) {
            case 1:
                $status = '未審核';
                break;
            case 2:
                $status = '不通過';
                break;
            case 3:
                $status = '通過';
                break;
        }
        return $status;
    }

    /** updateNoticeData
     *  更新 通知超連結、類別、關聯ID
     */
    public function updateNoticeData(int $notice_id, string $type, int $fk_id, string $href)
    {
        $update_notice_link = $this->updateNoticeLink($notice_id, $href);
        $update_notice_type = self::updateNoticeTypeAndFkId($notice_id, $type, $fk_id);
        $add_notice_user = self::updateNoticeUserTypeAndFkId($notice_id, $type, $fk_id);
    }
}
