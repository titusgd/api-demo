<?php

namespace App\Traits;
use App\Services\Service;
trait ResponseTrait
{
    private $response;

    public function getResponse():mixed
    {
        return $this->response;
    }

    public function setResponse(mixed $response): self
    {
        $this->response = $response;
        return $this;
    }
    /**setOk($code, $message,$data=[])
     * 設定回傳訊息
     * @param $data 要回傳的資料
     * @param $page_info 分頁資訊
     * @return void
     */
    public function setOk($data = null, $page_info = null)
    {
        
        ($data == null) && $this->setResponse((new Service)->response('00', 'ok'));
        ((!empty($data)) && $page_info==null) && $this->setResponse((new Service)->response('00', 'ok', $data));
        (!empty($page_info)) && $this->setResponse((new Service)->response_paginate('00', 'ok', $data, $page_info));
    }
    /**setErr($code, $message,$data=[])
     * 設定回傳錯誤訊息
     */
    public function setErr($code, $message, $data = [])
    {
        $this->setResponse((new Service)->response($code, $message, $data));
    }
}
