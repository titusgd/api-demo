<?php

namespace App\Http\Controllers\Offices;

use App\Http\Controllers\Controller;
use App\Services\Office\ToWord;
use App\Services\Office\ToPDFService;
// ----- methods -----
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
class ToWordController extends Controller
{

    // 舊有請求參數備註
    // 出團名稱  s_total
    // 主題名稱  V2
    // 主題附註  V3
    // 行程特色  V5  主頁圖片 V13 
    // 行程內容  V11  特別說明 View_memo_1  溫馨提醒  View_memo_2
    // 餐食  V12
    // 景點內容  View   景點介紹  View_introduce  景點圖片  View_image
    // 注意事項  V6
    // 旅遊注意事項  V20
    // 優惠顯示  R3(1:enable 2:disable)
    // 直售價格  price
    // 業務員資料  V7

    // 請求格式化
        // $res = $request->toArray();
        // dd($res);
        // 不確定 V3
        // 目前廢棄不用，備註 V14，行程中圖片 V9
        // $name = [
        //     "s_total" => "travel_name",   // 出團名稱
        //     "V2" => "travel_slogan",  // 主題名稱
        //     "V3" => "travel_slogan_note",  // 主題備註
        //     "V5" => "schedule_feature",   // 行程特色
        //     "V13" => "schedule_feature_image",    // 行程特色-主頁圖片
        //     "V11" => "schedule_content",  // 日行程內容
        //     "View_memo_1" => "schedule_special_note", //日行程特別說明,
        //     "View_memo_2" => "schedule_kind_reminder",  //溫馨提醒
        //     "V12" => "schedule_serve", // 餐食
        //     "View" => "schedule_attraction", //景點內容
        //     "View_introduce" => "schedule_attraction_introduce", // 景點介紹
        //     "View_image" => "schedule_attraction_image",  // 景點圖片
        //     "V6" => "please_note",    // 注意事項
        //     "V20" => "travel_please_note", // 旅遊注意事項
        //     "R3" => "discount",  // 顯示優惠
        //     "price" => "price",  // 直售價格
        //     "V7" => "sales_data"   // 業務員資料
        // ];

    public function word(Request $request){
        $dt = date("Y-m-d H:i:s");
        $query = http_build_query($request->toArray());
        return view("download.word",['query'=>$query,'data'=>$request->all()]);
    }
    public function word_download(Request $request){
        $service = new ToWord($request->id,[],false);
        $download = $service->downloadPath();
        $headers = array(
            "Cache-Control" => 'no-cache, no-store, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1',
            'Content-Type: application/docx',
        );
        return response()->download($download['file_path'],$download['file_name'], $headers);
    }
    public function word_convert(Request $request)
    {
        
        $show_info = collect([]);
        
        $show_info
            ->put("travel_name", (!empty($request->V1)) ? true : false)
            ->put("travel_slogan", (!empty($request->V2)) ? true : false)
            ->put("travel_slogan_note", (!empty($request->V3)) ? true : false)
            ->put("schedule_feature", (!empty($request->V5)) ? true : false)
            ->put("schedule_feature_image", (!empty($request->V13)) ? true : false)
            ->put("schedule_content", (!empty($request->V11)) ? true : false)
            ->put("schedule_special_note", (!empty($request->View_memo_1)) ? true : false)
            ->put("schedule_kind_reminder", (!empty($request->View_memo_2)) ? true : false)
            ->put("schedule_serve", (!empty($request->V12)) ? true : false)
            ->put("schedule_attraction", (!empty($request->View)) ? true : false)
            ->put("schedule_attraction_introduce", (!empty($request->View_introduce)) ? true : false)
            ->put("schedule_attraction_image", (!empty($request->View_image)) ? true : false)
            ->put("please_note", (!empty($request->V6)) ? true : false)
            ->put("travel_please_note", (!empty($request->V20)) ? true : false)
            ->put("discount", (($request->R3 == '1')) ? true : false)
            ->put("price", (!empty($request->V10)) ? true : false)
            ->put("sales_data", (!empty($request->V7)) ? true : false);
            
        $service = new ToWord($request->id,$show_info);
        $service->addTitleStyle('3', ['color' => '65F2FC']);
        $service->removeTitleStyle(3);
        $file_path = $service->createDocx($show_info->toArray());
        
        $headers = array(
            "Cache-Control" => 'no-cache, no-store, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1',
            // 'Content-Type' => 'application/json; charset=utf-8',
            'Content-Type: application/docx',
        );
        return response()->json(['code'=>'00','msg'=>'ok','data'=>["id"=>$request->id,'execute_time'=>$service->getExecuteTime()]],200);
        // return response()->download($file_path[0], $file_path[1] . '.docx', $headers);
    }

    public function pdf(Request $request){
        $query = http_build_query($request->toArray());
        return view("download.pdf",['query'=>$query,'data'=>$request->all()]);
    }
    public function pdf_download(Request $request){
        $service = new ToPDFService($request->id,[],false);
        $download = $service->downloadPath();
        $headers = array(
            "Cache-Control" => 'no-cache, no-store, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1',
            'Content-Type: application/docx',
        );
        return response()->download($download['file_path'],$download['file_name'], $headers);
        
    }
    public function pdf_convert(Request $request)
    {
        // phpinfo();
        $show_info = collect([]);
        $show_info
            ->put("travel_name", (!empty($request->V1)) ? true : false)
            ->put("travel_slogan", (!empty($request->V2)) ? true : false)
            ->put("travel_slogan_note", (!empty($request->V3)) ? true : false)
            ->put("schedule_feature", (!empty($request->V5)) ? true : false)
            ->put("schedule_feature_image", (!empty($request->V13)) ? true : false)
            ->put("schedule_content", (!empty($request->V11)) ? true : false)
            ->put("schedule_special_note", (!empty($request->View_memo_1)) ? true : false)
            ->put("schedule_kind_reminder", (!empty($request->View_memo_2)) ? true : false)
            ->put("schedule_serve", (!empty($request->V12)) ? true : false)
            ->put("schedule_attraction", (!empty($request->View)) ? true : false)
            ->put("schedule_attraction_introduce", (!empty($request->View_introduce)) ? true : false)
            ->put("schedule_attraction_image", (!empty($request->View_image)) ? true : false)
            ->put("please_note", (!empty($request->V6)) ? true : false)
            ->put("travel_please_note", (!empty($request->V20)) ? true : false)
            ->put("discount", (($request->R3 == '1')) ? true : false)
            ->put("price", (!empty($request->V10)) ? true : false)
            ->put("sales_data", (!empty($request->V7)) ? true : false);

        $service = new ToPDFService($request->id, $show_info->toArray());

        $file_path = $service->create();
        $headers = array(
            "Cache-Control" => 'no-cache, no-store, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1',
            // 'Content-Type' => 'application/json; charset=utf-8',
            'Content-Type: application/pdf',
        );
        return response()->json(['code'=>'00','msg'=>'ok','data'=>["id"=>$request->id]],200);
    }
    public function pdf_img(Request $request){
        $fullPath=storage_path().'/travel/temp/images/'.$request->text;

        return response()->stream(function () use ($fullPath) {
            echo file_get_contents($fullPath);
        }, 200, ['Content-Type' => 'image/jpeg']);
    }
    public function img(Request $request){
        $fullPath=storage_path().'/travel/imgs/promotions/'.$request->text;
        return response()->stream(function () use ($fullPath) {
            echo file_get_contents($fullPath);
        }, 200, ['Content-Type' => 'image/jpeg']);
    }

    public function del(Request $request)
    {
        $file_name = $request->travel_no;
        $path = storage_path('travel\\temp\\') . $file_name;
        $temp_arr = collect(['html', 'docx', 'pdf']);
        $temp_arr->map(function ($item) use ($path) {
            if (file_exists($path . '.' . $item)) {
                unlink($path . '.' . $item);
            }
        });
    }

    public function del_all(){
        $path = storage_path('travel/temp');
        $files = collect();
        foreach (scandir($path ) as $item) {
            $files->push($item);
        }
        $files = $files->filter(function($item,$key){
            return ($item!='.'&&$item!='..');
        });
        $files->map(function($item)use($path) {
            unlink($path . '/' . $item);
        });
    }
}
