<?php

namespace App\Http\Controllers\Error;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ErrorReport;
// 資料驗證
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades;
use Illuminate\Support\MessageBag;
use App\Services\Errors\ReportService;
use App\Services\Files\ImageUploadService;
use App\Models\Image;
class ErrorReportController extends Controller
{
    //

    public function index(Request $request){
        $headers = array('Content-Type' => 'application/json; charset=utf-8');
        // -------------------------------- 取得input當前分頁 --------------------------------
        $limit = $request->count;   // req->count 每頁筆數

        // 寫入request請求內
        if(!isset($limit)) $limit = 20;

        // -------------------------------- 使用Eloquent 查詢並取得資料 --------------------------------
        $data_list = ErrorReport::select(
            "id",
            "created_at as date",
            "os",
            "browser",
            "error_url as url",
            "size",
            "error_image as image",
            "describe",
            "status"
            )->paginate($limit);
        

        // -------------------------------- 輸出資料設定 --------------------------------
        $current_page = $data_list->lastPage(); //總頁數
        $total=$data_list->total();             //總筆數
        $sn = $data_list->currentPage();        //當前頁次
        $code = "00";                            //錯誤代碼 : 00 成功
        $msg = "ok";                            // 00:ok
        // -------------------------------- 資料表查詢 格式化--------------------------------
        $data = $data_list->items();
        $list_count = count($data);
        $errorService = new ReportService();
        // 資料表欄位重製處理
        for($i=0;$i<$list_count;$i++){
            // 依據前端"特殊"的要求，所做日期特殊轉換
            $data[$i]['date'] = $errorService->dateFormat($data[$i]['date']);
            // 型態轉換輸出成文字
            $data[$i]['status'] = $errorService->statusToString($data[$i]['status']);
        }
        
        // -------------------------------- 製作回傳格式------------------------
        $res = [
            "code"=>$code,
            "msg"=>'ok',
            "page"=>[
                "total"=>$current_page,      // 總頁數
                "countTotal"=>$total,            // 總筆數
                "page"=>$sn,                  // 頁次
                "count"=>$limit
            ],
            "data"=>$data
            

        ];
        return response()->json($res,200,$headers,JSON_UNESCAPED_UNICODE);
    }
    public function store(Request $request)
    {        
        $service = new ReportService();
        // 將請求轉成陣列
        $req = $service->requestToArray($request);
        // 錯誤回傳
        $validator = $service->violationCreate($req);
        if($validator->fails()){
            $message = explode(' ',$validator->errors()->first());
            return $service->response($message[1],$message[0]);
        }
        // -------------------------------- 圖片處理 --------------------------------
        // 取得圖片
        $image_source = $req['image'];
        // base64解碼
        $image_service = new ImageUploadService();
        $image_data = $req['image'];
        $image_service->addImage($image_data,'Error');
        $image_id = $image_service->getId();
        $image = Image::find($image_id);
        
        // ----------------------------------------------------------------

        $data =[
            'describe'=>$req['describe'],
            'os'=>$req['os'],
            'browser'=>$req['browser'],
            'error_url'=>$req['url'],
            'error_image'=>$image['url'],
            'size'=>$req['size'],
            'status'=>1,               // 錯誤案件處理狀態
            'user_id'=>auth()->user()->id,             // 填寫錯誤案件人員
            'programmer_id'=>0, // 未寫入->代表未指派給工程師處理
        ];

        $error_id = $service->create($data);
        $image->fk_id = $error_id['id'];
        $image->save();
        return $service->response("00","ok");
    }

    public function update(Request $request,ErrorReport $errorReport){
        $service = new ReportService();
        // 資源確認
        if(!$service->checkId($request->id)) return $service->response("02","id");
        // 資料轉換
        $req = $service->requestToArray($request);
        // 資料格式化
        $data = $service->dataFormat($req);

        $validator = $service->validator($data);
        if($validator->fails()){
            // 取得第一筆錯誤
            $message = $validator->errors()->first();
            //回傳錯誤
            return $service->response("01",$message);
        }
        $service->update($data,$request->id);
        return $service->response("00","ok");
    }
    
}
