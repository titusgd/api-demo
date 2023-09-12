<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

/**
 * KkdayTrait kkday專用呼叫api
 * @method self callApi(string $method = 'get', string $uri, int|string $model = "DEV") 執行呼叫api
 * @method array getBody() 取得回傳結果的body
 * @method self setParams(array $params) 設定 params
 * @method array getParams() 取得params 設定參數
 * @method self setBearToken(string $bearToken) 設定 api的 bear token參數
 * @method string getBearToken() 取得api 的 brar token 參數
 * @method self setHeaders(array $headers) 設定 api header
 * @method array getHeaders() 取得 api header
 */
trait KkdayTrait
{
    private $urlTest = "https://api-b2d.sit.kkday.com";
    private $urlDev = "https://api-b2d.sit.kkday.com";
    private $url = "https://api-b2d.kkday.com";
    private $headers = [
        "Content-Type" => "application/json"
    ];
    private $params;
    private $bearToken = '';
    private $response, $body, $json;
    private $apiUrl, $method;
    /**
     * callApi()
     * @param string $method "GET" | "POST"
     * @param string $uri
     * @param string $model code:1 TEST(測試)|2 PRODUCTION (正式) (Production)|3 DEV(開發)
     */
    public function callApi(string $method = 'get', string $uri, int|string $model = "PRODUCTION")
    {
        (empty($bearToken)) && $this->defaultTokenJson();

        $url = $this->getApiUrl($model) . '/' . $uri;
        $this->headers['Authorization'] = 'Bearer ' . $this->bearToken;
        $this->apiUrl = $this->getApiUrl($model) . '/' . $uri;
        $this->method = $method;
        return $this;
    }
    /**
     * getBody()
     * 取得response body
     */
    public function getBody(): array
    {
        $method = $this->method;
        $http = Http::withHeaders($this->headers);
        $this->response = $http->$method($this->apiUrl, $this->params);
        $this->body = $this->response->json();
        return $this->body;
    }
    /**
     * getJson()
     * 取得response body
     */
    public function getJson(): object
    {
        return $this->json;
    }
    /**
     * getApiResponse 取得呼叫api 後回傳的response
     */
    public function getApiResponse(): object
    {
        return $this->response;
    }
    /**
     * getApiUrl 取得api的rul
     * @param string $model code:1 "TEST"(測試)|2 "PRODUCTION"(正式)|3 "DEV"(開發)
     */
    private function getApiUrl(int|string $method): string
    {
        $url = "";
        switch ($method) {
            case "TEST":
            case 1:
                $url = $this->urlTest;
                break;
            case "PRODUCTION":
            case 2:
                $url = $this->url;
                break;
            case "DEV":
            case 3:
            default:
                $url = $this->urlDev;
                break;
        }
        return $url;
    }
    // getter and setter
    public function getParams(): array
    {
        return $this->params;
    }
    public function setParams(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    public function getBearToken(): string
    {
        return $this->bearToken;
    }
    public function setBearToken(string $bearToken): self
    {
        $this->bearToken = $bearToken;
        return $this;
    }
    // url.tets
    public function getTestUrl(): string
    {
        return $this->urlTest;
    }

    public function setTestUrl(string $url): self
    {
        $this->urlTest = $url;
        return $this;
    }
    // url.dev
    public function getDevUrl(): string
    {
        return $this->urlDev;
    }
    public function setDevUrl(string $url): self
    {
        $this->urlDev = $url;
        return $this;
    }
    // // url.production
    // public function getUrl(): string
    // {
    //     return $this->url;
    // }
    // public function setUrl(string $url): self
    // {
    //     $this->url = $url;
    //     return $this;
    // }
    // headers
    public function getHeaders(): array
    {
        return $this->headers;
    }
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }
    private function defaultTokenJson(): self
    {
        $json = file_get_contents(base_path() . '/app/Traits/kkday.conf');
        $data = json_decode($json, true);
        $this->setBearToken($data['token']);
        return $this;
    }
    public function resetKey()
    {
        $json = file_get_contents(base_path() . '/app/Traits/kkday.conf');
        $data = json_decode($json, true);
        // 加密設定
        $tk = base64_encode(hash('sha256', '70381925'));

        switch (env("KKDAY_MODEL")) {
            case "DEV":
                $url = $this->urlDev;
                break;
            case "TEST":
                $url = $this->urlTest;
                break;
            case "PRODUCTION":
                $url = $this->url;
                break;
        }
        // // 呼叫api
        // $response=Http::withHeaders([
        //     "Content-Type" => "application/json"
        // ])->post(
        //     $url.'/v3/ReGetApiKey',
        //     [
        //         "uuid"=>$data['uuid'],
        //         "token"=>$tk,
        //     ]
        // );
        // $body = $response->json();
        // // dd($body);

        // $new_data['uuid'] = $data['uuid'];
        // $path = base_path() . '/app/Traits/kkday2.conf';
        // if (!is_writable(dirname($path))) {
        //     mkdir(dirname($path), 0755, true);
        // }
        // $data = json_encode(["aaa" => "bbb", "CCC" => "DDDD"]);
        // file_put_contents($path, $data);
        // dd($tk, $data, base_path() . '/app/Traits/kkday2.conf');
        // $this->setBearToken($data['token']);
    }
}
