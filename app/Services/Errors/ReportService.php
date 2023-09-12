<?php

namespace App\Services\Errors;

use Illuminate\Support\Facades\Validator;
// use App\Models\Account\Access;
use App\Models\ErrorReport;
use Illuminate\Support\Facades\Auth;

class ReportService
{

    private $headers = array('Content-Type' => 'application/json; charset=utf-8');
    /** dateFormat
     *  日期格式化並輸出陣列
     *  @param string $date 日期
     *  @return array
     */
    public function dateFormat($date)
    {
        $date = explode(' ', str_replace('-', '/', $date));
        return $date;
    }
    /** statusToString()
     *  狀態碼轉文字
     *  @param string $str 狀態碼
     *  @return string
     */
    public function statusToString($str)
    {
        $status_str = "";
        switch ($str) {
            case 1:
                $status_str = "pending";
                break;
            case 2:
                $status_str = "processing";
                break;
            case 3:
                $status_str = "solved";
                break;
        }
        return $status_str;
    }

    public function validator($data)
    {
        $validator = Validator::make($data, [
            'describe' => [
                'required',
                'string'
            ],
            'browser' => 'required|string',
            'os' => 'required|string',
            'error_url' => 'required|string',
            // TODO:之後需做圖片驗證
            // 'error_image'=>'required|string',
            'size' => 'required|string'
        ], [
            'describe.required' => "describe",
            'browser.required' => "browser",
            'os.required' => "os",
            'error_url.required' => "url",
            'size.required' => "size",
            'describe.string' => "describe",
            'describe.string' => "describe",
            'browser.string' => "browser",
            'os.string' => "os",
            'error_url.string' => "error_url",
            'size.string' => "size",
        ]);

        return $validator;
    }

    public function requestToArray($request)
    {
        $req = json_decode($request->getContent(), true);
        return $req;
    }

    public function create($data)
    {
        $id = ErrorReport::create(
            [
                "describe" => $data['describe'],
                "os" => $data['os'],
                "browser" => $data['browser'],
                "error_url" => $data['error_url'],
                "error_image" => $data['error_image'],
                "size" => $data['size'],
                "status" => 1,               // 錯誤案件處理狀態
                "user_id" => Auth::user()->id,             // 填寫錯誤案件人員
                "programmer_id" => 0, // 未寫入->代表未指派給工程師處理
            ]
        );
        return $id;
    }

    public function response($code, $message = "", $data = [])
    {
        $res = [
            "code" => $code,
            "msg" => $message,
            "data" => $data
        ];
        // return "123";
        return response()->json($res, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }

    public function dataFormat($req)
    {
        $data = [
            "describe" => $req['describe'],
            "os" => $req['os'],
            "browser" => $req['browser'],
            "error_url" => $req['url'],
            // TODO: 尚未完成圖片上傳處理，之後需要完成。
            // 目前暫時先輸入空值
            "error_image" => $req['url'],
            "size" => $req['size'],
            // TODO:寫入使用者、工程師ID
            "status" => 1,               // 錯誤案件處理狀態
            "user_id" => 0,             // 填寫錯誤案件人員
            "programmer_id" => 0, // 未寫入->代表未指派給工程師處理
        ];

        return $data;
    }


    public function checkId($id)
    {
        $check = ErrorReport::where('id', '=', $id)->first();
        return ($check) ? true : false;
    }

    public function update($data, $id)
    {
        $updated = ErrorReport::where('id', '=', $id)->update($data);
    }

    public function violationCreate($req)
    {
        $validator = Validator::make($req, [
            "describe" => "required|string",
            "os" => "required|string",
            "browser" => "required|string",
            "url" => "required|string",
            "image" => "required|string",
            "size" => "required|string"
        ], [
            "describe.required" => "describe 01",
            "describe.string" => "describe 01",
            "os.required" => "os 01",
            "os.string" => "os 01",
            "browser.required" => "browser 01",
            "browser.string" => "browser 01",
            "url.required" => "url 01",
            "url.string" => "url 01",
            "image.required" => "image 01",
            "image.string" => "image 01",
            "size.required" => "size 01",
            "size.string" => "size 01",

        ]);
        return $validator;
    }
}
