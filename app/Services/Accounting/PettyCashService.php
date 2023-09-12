<?php

namespace App\Services\Accounting;

use App\Services\Service;
use Illuminate\Support\Facades\Validator;
use App\Models\PettyCash\PettyCash;
use App\Services\Files\ImageUploadService;
use App\Services\PettyCash\PettyCashService as PPCS;
use App\Services\PettyCash\PettyCashListService as PCLS;
use Illuminate\Support\Facades\DB;

use App\Models\Image;
use App\Models\Store;

class PettyCashService extends Service
{

    private $req, $request;
    private $vali_update = [
        "id" => "required|integer|exists:petty_cashes,id",
        "status" => "required|string",
    ];
    private $error = [
        "id.required" => "01 id",
        "id.integer" => "01 id",
        "id.exists" => "02 id",

        "status.required" => "01 status",
        "status.string" => "01 status",

    ];
    private $status_key = ["all", "apply", "operation", "provide", "receive", "fail"];
    function __construct($req)
    {
        $this->request = $req;
        $this->req = Service::requireToArray($req);
    }

    // 資料檢測
    public function validate()
    {
        $vali = Validator::make($this->req, $this->vali_update, $this->error);
        $error = $this->checkValiDate($vali);
        if ($error) return $error;

        if (!in_array($this->req['status'], $this->status_key)) return Service::response("01", "status");

        if ($this->req['file'] === "") return Service::response("300", "file");
    }

    /** checkValiDate() 
     *  回傳錯誤訊息
     */
    public function checkValiDate($validate)
    {
        if ($validate->fails()) {
            list($code, $message) = explode(" ", $validate->errors()->first());
            return Service::response($code, $message);
        }
    }

    public function updatePettyCashList()
    {
        $petty_cash = PettyCash::find($this->req['id']);
        $petty_cash->status = Service::getStatusKey($this->req['status']);
        $petty_cash->save();

        if ($this->req['file']) {
            foreach($this->req['file'] as $k => $v) {
                // 取得圖片
                $image_source = $v;
                // base64解碼
                $image_service = new ImageUploadService();
                if (!$image_service->checkImageExtension($image_source)) return Service::response("300", "file");

                // $image = Image::where('type', '=', 'PettyCashAccounting')->where('fk_id', '=', $this->req['id'])->first();
                // ($image)&& $image_service->deleteImageFile($this->req['id'], 'PettyCashAccounting', 3);

                $image_data = $v;
                $image_service->addImage($image_data, "PettyCashAccounting", $this->req['id']);
                $image_id = $image_service->getId();
            }
        }
        return true;
    }

    public function validateApply()
    {
        $relus = [
            "count" => "required|integer",
            "page" => "required|integer",
        ];
        $message = [
            "date.start.string" => "01 start",
            "date.start.date" => "01 start",

            "date.end.string" => "01 end",
            "date.end.date" => "01 end",

            "status.string" => "01 status",

            "store.integer" => "01 store",
            "store.exists" => "02 store",

            "count.required" => "01 count",
            "count.integer" => "01 count",
            "page.required" => "01 page",
            "page.integer" => "01 page"
        ];

        (!empty($this->req['date']['start'])) && $reluse["date.start"] = "string|date";
        (!empty($this->req['date']['end'])) && $reluse["date.end"] = "string|date";

        (!empty($this->req['status'])) && $reluse["status"] = "string";
        (!empty($this->req['store'])) && $reluse["store"] = "integer";

        $vali = Service::validatorAndResponse($this->req, $relus, $message);

        // 驗正
        if ($vali)  return $vali;

        if (!empty($this->req['store'])) {
            $store = Store::find($this->req['store']);
            if (!$store) return Service::response("02", "store");
        }

        // 轉換 狀態碼代碼
        if (!empty($this->req["status"])) {
            $status_key = Service::getStatusKey($this->req["status"]);
            if (!$status_key) return Service::response("01", "status");
        }
    }
    /** applyData()
     *  查詢
     *  @return string|array
     */
    public function applyData()
    {
        $req = $this->req;
        $headers = array('Content-Type' => 'application/json; charset=utf-8');
        // -------------------------------- date & store 選填 -------------------
        $start_date = (!empty($req['date']['start'])) ? $req['date']['start'] : '2020/1/1';
        $end_date = (!empty($req['date']['end'])) ? $req['date']['end'] : '2150/1/1';
        //----------------------------------------------------------------------
        // 每頁資料筆數
        $limit = (!isset($limit)) ? $req['count'] : 20;

        $page = $req['page'];
        $status = (!empty($req['status'])) ? Service::getStatusKey($req['status']) : 7;

        $petty_cach = PettyCash::select(
            'id',
            'status',
            'user_id',
            'store_id',
            DB::raw('(select `store` from `stores` where stores.id = store_id) as store_name'),
            DB::raw('(select `name` from `users` where users.id = user_id) as applicant'),
            'proposal_id',
            'price',
            'created_at',
            'updated_at'
        )
            ->where('created_at', '>=', $start_date . ' 00:00:00')
            ->where('created_at', '<=', $end_date . ' 23:59:59');

        // 分店
        (!empty($req['store'])) && $petty_cach->where('store_id', '=', $req['store']);

        ($status != 7) && $petty_cach->where('status', '=', $status);

        $petty_cach = $petty_cach->orderBy('created_at', 'desc');
        // ----------------------- 分頁設定 -------------------------------------
        // 取得分頁
        $petty_cach = $petty_cach->paginate($limit);

        $current_page = $petty_cach->lastPage(); //總頁數
        $total = $petty_cach->total();             //總筆數
        $sn = $petty_cach->currentPage();
        // ---------------------------------------------------------------------
        $petty_cach2 = PettyCash::select('status', 'price')
            ->where('created_at', '>=', $start_date . " 00:00:00")
            ->where('created_at', '<=', $end_date . " 23:59:59");
        (!empty($req['store'])) && $petty_cach2->where('store_id', '=', $req['store']);

        ($status != 7) && $petty_cach2->where('status', '=', $status);

        $petty_cach2 = $petty_cach2->orderBy('created_at', 'desc');
        $petty_cach2 = $petty_cach2->get();

        // 零用金總金額
        $sum = 0;
        // 筆數
        $count = count($petty_cach2);
        // 通過
        $pass = 0;
        // 未通過
        $fail = 0;
        foreach ($petty_cach2 as $val) {
            $sum += $val['price'];
            ($val['status'] != 8 & $val['status'] != 12) ? $pass++ : $fail++;
        }

        $data = [];

        foreach ($petty_cach as $val) {
            // PettyCashAccounting 會計 零用金 | PettyCash 店鋪 零用金
            $images = Image::where('type', '=', 'PettyCashAccounting')
                ->where('fk_id', '=', $val['id'])
                ->first();

            $temp = [
                'id' => $val['id'],
                'date' => Service::dateFormat($val["created_at"]),
                'price' => $val['price'],
                'applicant' => $val['applicant'],
                'proposal' => $val['proposal_id'],
                'type' => [
                    'date' => Service::dateFormat($val['updated_at']),
                    'status' => Service::getStatusValue($val['status']),
                ],
                'documents' => ($images !== null) ? $images['url'] : null,
            ];

            $temp['store'] = [
                'id' => $val['store_id'],
                'name' => $val['store_name'],
            ];

            array_push($data, $temp);
        }

        $res = [
            'code' => '00',
            'msg' => 'ok',
            'page' => [
                'total' => $current_page,          // 總頁數
                'countTotal' => $total,            // 總筆數
                'page' => $req['page'],            // 頁次
            ],

            'data' => [
                'info' => [
                    'sum' => $sum,
                    'apply' => $count,
                    'pass' => $pass,
                    'fail' => $fail
                ],
                'list' => $data
            ],
        ];

        return response()->json($res, 200, $headers, JSON_UNESCAPED_UNICODE);
    }

    public function validateDetail()
    {
        $error_message = [
            "date.required" => "01 date",

            "date.start.required" => "01 start",
            "date.start.date" => "01 start",

            "date.end.required" => "01 end",
            "date.end.date" => "01 end",

            "store.integer" => "01 store",
            "store.exists" => "02 store",

            "count.required" => "01 count",
            "count.integer" => "01 count",

            "page.required" => "01 page",
            "page.integer" => "01 page"
        ];

        $validator = [
            "date" => "required",
            "date.start" => "required|date",
            "date.end" => "required|date",
            "count" => "required|integer",
            "page" => "required|integer",
        ];

        (!empty($this->req['store'])) && $validator["store"] = "integer";

        $vali = Service::validatorAndResponse($this->req, $validator, $error_message);
        if ($vali) return $vali;
        // 檢查 store 
        if (!empty($this->req['store'])) {
            $store = Store::find($this->req['store']);
            if (!$store) return Service::response('02', 'store');
        }
    }

    public function list()
    {
        $pcls = new PCLS($this->request);

        $list = $pcls->searchList(true);

        return $list;
    }
}
