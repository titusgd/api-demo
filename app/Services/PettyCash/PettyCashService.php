<?php

namespace App\Services\PettyCash;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\Service;
use App\Models\Store;
use App\Models\PettyCash\PettyCash;
use App\Models\Image;

class PettyCashService extends Service
{

    private $vali_error_message = [
        "date.start.date" => "01 start",
        "date.start.string" => "01 start",
        "date.end.date" => "01 end",
        "date.end.string" => "01 end",

        "status.string" => "01 status",

        "store.required" => "01 store",
        "store.integer" => "01 store",
        "store.exists" => "02 store",

        "count.required" => "01 count",
        "count.integer" => "01 count",

        "page.required" => "01 page",
        "page.integer" => "01 page",

        "price.required" => "01 price",
        "price.integer" => "01 price",
        "price.max" => "01 price",
        "price.between" => "01 price",
        "price.numeric" => "01 price",
        "proposal.integer" => "01 proposal"

    ];

    private $apply = [
        "store" => "required|integer",
        "price" => "required|numeric|between:0,99999999.99",
    ];

    private $recordVali = [
        "store" => "required|integer",
        "count" => "required|integer",
        "page" => "required|integer",
    ];


    public function validate($req, $type = "apply")
    {
        switch ($type) {
            case "apply":
                (!empty($req['proposal'])) && $this->apply["proposal"] = "integer";

                $validate = Validator::make($req, $this->apply, $this->vali_error_message);
                break;
            case "record":
                (!empty($req['date']['start'])) && $this->recordVali["date.start"] = "string|date";

                (!empty($req['date']['end'])) && $this->recordVali["date.end"] = "string|date";

                (!empty($req['status'])) && $this->recordVali["status"] = "string";

                $validate = validator($req, $this->recordVali, $this->vali_error_message);
                break;
        }
        // $validate = Validator::make($req, $this->apply, $this->vali_error_message);
        return $validate;
    }

    public function checkStoreId($id)
    {
        $store = Store::select("id")->where("id", "=", $id)->first();
        return ($store) ? true : false;
    }

    public function create($req)
    {
        $user_id = Service::getUserId();;
        $price = explode(".", $req["price"]);
        (count($price) != 1) && $price[1] = substr($price[1], 0, 2);
        $price = implode(".", $price);
        // 資料欄位名稱調整與資料表欄位一致
        $data = [
            "auditor_id" => 0,
            "user_id" => $user_id,
            "price" => $price,
            "status" => 8,
            "store_id" => $req["store"]
        ];
        // 選填
        // TODO: 自動新增申議書
        // 目前沒有輸入自動帶0。
        $data["proposal_id"] = (!empty($req['proposal'])) ? $req["proposal"] : 0;

        $petty_cach = PettyCash::create($data);

        return $petty_cach;
    }

    public function recordVali($req)
    {
        $vali = $this->validate($req, "record");

        if (!empty($req['store'])) {
            $store = Store::find($req['store']);
            if (!$store) return Service::response("02", 'store');
        }

        if ($this->checkValiDate($vali)) return $this->checkValiDate($vali);
    }


    public function searchPettyCashApply($req, $store_info = false)
    {
        $headers = array('Content-Type' => 'application/json; charset=utf-8');
        // 日期
        $start_date = (!empty($req["date"]["start"])) ? $req["date"]["start"] : "2020/1/1";
        $end_date = (!empty($req["date"]["end"])) ? $req["date"]["end"] : "2150/1/1";

        $store_id = $req["store"];

        $limit = (!empty($req['count'])) ? $req['count'] : 20;

        $page = $req["page"];
        // 預設狀態 如果預期外的，一律帶7:所有
        $status = (!empty($req['status'])) ? Service::getStatusKey($req["status"]) : 7;

        // TODO: 缺申議書查詢
        $petty_cach = PettyCash::select(
            "id",
            "status",
            "user_id",
            "store_id",
            DB::raw("(select `store` from `stores` where stores.id = store_id) as store_name"),
            DB::raw("(select `name` from `users` where users.id = user_id) as applicant"),
            "proposal_id",
            "price",
            "created_at",
            "updated_at"
        )
            ->where("created_at", ">=", "{$start_date} 00:00:00")
            ->where("created_at", "<=", "{$end_date} 23:59:59");
        ($req['store'] !== 0) && $petty_cach = $petty_cach->where("store_id", "=", $store_id);

        ($status != 7) && $petty_cach->where("status", "=", $status);
        // 排序
        $petty_cach->orderBy('created_at', 'desc');
        // 分頁
        $petty_cach = $petty_cach->paginate($limit);

        $current_page = $petty_cach->lastPage();    // 總頁數
        $total = $petty_cach->total();              // 總筆數
        $sn = $petty_cach->currentPage();           // 頁次

        $petty_cach2 = PettyCash::select("status", "price")
            ->where("created_at", ">=", "{$start_date} 00:00:00")
            ->where("created_at", "<=", "{$end_date} 23:59:59");

        ($req['store'] !== 0) &&  $petty_cach2 = $petty_cach2->where("store_id", "=", $store_id);

        ($status != 7) && $petty_cach->where("status", "=", $status);

        $petty_cach2 = $petty_cach2->get();
        // 零用金金額
        $sum = 0;
        // 使用筆數
        $count = count($petty_cach2);
        $pass = 0;
        $fail = 0;
        foreach ($petty_cach2 as $val) {
            $sum += $val["price"];
            ($val['status'] != 8 & $val['status'] != 12) ? $pass++ : $fail++;
        }

        $data = [];

        foreach ($petty_cach as $val) {
            // PettyCashAccounting 會計 零用金 | PettyCash 店鋪 零用金
            $images = Image::where([
                ["type", "=", "PettyCashAccounting"],
                ["fk_id", "=", $val["id"]]
            ])
                // $images = Image::where("type", "=", ($store_info) ? "PettyCashAccounting" : "PettyCash")
                // ->where("fk_id", "=", $val["id"])
                ->first();
            $temp = [
                "id" => $val['id'],
                "date" => Service::dateFormat($val["created_at"]),
                "price" => $val["price"],
                "applicant" => $val['applicant'],
                "proposal" => $val['proposal_id'],
                "type" => [
                    // TODO:匯款單據https，尚未實作
                    "date" => Service::dateFormat($val['updated_at']),
                    "status" => Service::getStatusValue($val['status']),
                ],
                // TODO:匯款單據https，尚未實作
                "documents" => ($images !== null) ? $images["url"] : null,
            ];
            if ($store_info) {
                $temp["store"] = [
                    "id" => $val['store_id'],
                    "name" => $val['store_name'],
                ];
            }
            array_push($data, $temp);
        }

        $res = [
            "code" => "00",
            "msg" => "ok",
            "page" => [
                "total" => $current_page,          // 總頁數
                "countTotal" => $total,            // 總筆數
                "page" => $req["page"],            // 頁次
            ],

            "data" => [
                "info" => [
                    "sum" => $sum,
                    "apply" => $count,
                    "pass" => $pass,
                    "fail" => $fail
                ],
                "list" => $data
            ],
        ];
        if ($store_info) return $res;
        return response()->json($res, 200, $headers, JSON_UNESCAPED_UNICODE);
    }
    /** checkValiDate() 
     *  回傳錯誤訊息
     *  @param object $validate
     *  @return string
     */
    public function checkValiDate($validate)
    {
        if ($validate->fails()) {
            list($code, $message) = explode(" ", $validate->errors()->first());
            return Service::response($code, $message);
        }
    }
}
