<?php
namespace App\Traits;

trait CustomResponseTrait{
    private $headers = array('Content-Type' => 'application/json; charset=utf-8');
    /** response()
     *  response 帶分頁資訊
     *  @param string $code 代碼
     *  @param string $message 代碼資訊
     *  @param array $data 資料
     */
    public function response($code, $message = '', $data = [])
    {
        $res = [
            'code' => $code,
            'msg' => $message,
            'data' => $data
        ];
        return response()->json($res, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }
    /** response_paginate()
     *  response 帶分頁資訊
     *  @param string $code 代碼
     *  @param string $message 代碼資訊
     *  @param array $data 資料
     *  @param array $page 分頁資訊
     */
    public function response_paginate($code, $message = '', $data = [], $page = [])
    {
        $res = [
            'code' => $code,
            'msg' => $message,
            'page' => $page,
            'data' => $data
        ];
        return response()->json($res, 200, $this->headers, JSON_UNESCAPED_UNICODE);
    }
    /** setHeader()
     *  重新設定response header
     *  @param array $arr header array
     *  @return viod
     * 
     */
    public function setHeader($arr){
        $this->header = $arr;
    }

    /** getHeader()
     *  取得response header設定
     *  @return array header
    */
    public function getHeader(){
        return $this->headers;
    }
}