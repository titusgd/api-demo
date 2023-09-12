<?php

namespace App\Services\Office;

use App\Services\Service;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class OfficeService extends Service
{
    private $execution_time;
    private $CURLOPT_RESOLVE = [
        'images-*.cdn.hinet.net:443:*',
        'images-*.cdn.hinet.net:443:*'
    ];
    public function setExecuteStart($name)
    {
        $this->execution_time[$name]['start'] = microtime(true);
        return $this;
    }
    public function setExecuteEnd($name)
    {
        $this->execution_time[$name]['end'] = microtime(true);
        $this->execution_time[$name]['time'] = $this->execution_time[$name]['end'] - $this->execution_time[$name]['start'];
        return $this; 
    }
    public function getExecutionTime()
    {
        return $this->execution_time;
    }

    public $patterns, $image_settings;
    function __construct()
    {
        $this->patterns = collect();
        $this->image_settings = collect();
        $this->image_settings->put('base_path', storage_path() . '/travel/temp/');
    }

    public function brFormat($str): object
    {
        $replace = '<br />';
        $search = collect([
            '<br/>', '<br>', '</br>', '<BR>', '</BR>', '<BR/>', '</ BR>',
            '<BR />', '<Br>', '</Br>', '<Br/>', '</ Br>', '<Br />', '<br />'
        ]);

        $search->map(function ($item) use (&$str, $replace) {
            $str = str_replace($item, $replace, $str);
        });

        $str = collect(explode('<br />', $str));
        return $str;
    }
    public function imageDownload($url, $path = 'images/')
    {
        // TODO: 轉檔
        // 修改完成需移除註解
        if (!empty($url)) {
            $url = "https:" . $url;
            $path = $this->image_settings['base_path'] . $path;
            // ---------- 使用 http 請求圖片 ----------
            $response = Http::withOptions([
                'curl' => [
                    CURLOPT_RESOLVE => $this->CURLOPT_RESOLVE,
                    // CURLOPT_RESOLVE => [
                    //     'images-*.cdn.hinet.net:443:203.66.32.139',
                    //     'images-*.cdn.hinet.net:443:203.66.32.76'
                    // ], // 設置 DNS 解析器
                ],
            ])->get($url);
            // 狀態ok則寫入圖檔
            // dd($response->body());
            if ($response->ok()) {
                $filename = trim(last(explode('/', $url)));
                $resource = fopen($path . $filename, 'w');
                fwrite($resource, $response->body());
                fclose($resource);
            }
            // 狀態 404
            if($response->status()===404){
                return [
                    'image_size' => 0,
                    'unit' => 'kb',
                    'path' => '',
                    'name' => '',
                    'file_path' => ''
                ];
            }
            // dd($url);
            // ---------- end http ----------
            // // ---------- 使用curl取得圖片 ----------
            // $ch = curl_init();
            // curl_setopt($ch, CURLOPT_URL, $url);
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            // curl_setopt($ch, CURLOPT_TIMEOUT, 0);
            // $file = curl_exec($ch);
            // // $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

            // //關閉串流
            // curl_close($ch);
            // $filename = pathinfo($url, PATHINFO_BASENAME);
            // $resource = fopen($path . $filename, 'w');
            // fwrite($resource, $file);
            // fclose($resource);
            // // ---------- end ----------
            // dd($path , $filename);
            if(empty($filename)) dd($url);
            $file_path = $path . $filename;
            // 當http出現預期外的狀況時，嘗試使用wget取得圖片
            if (filesize($file_path) == 0) {
                unlink($file_path);
                exec('cd /var/www/*/api-dev/storage/travel/temp/images && wget ' . $url);
            }
            // 檢查圖片的有效性，無效的圖片回傳空的
            if (!(@getimagesize($file_path))) {
                return [
                    'image_size' => 0,
                    'unit' => 'kb',
                    'path' => '',
                    'name' => '',
                    'file_path' => ''
                ];
            }

            if ($this->is_image($file_path)) {
                $file_size = filesize($path . $filename);

                $file_path = $path . $filename;
                // 確認檔案是否為jpg，如果不是，則轉成png
                $this->changeImage($file_path);
                $sourceFile = $file_path;   // 原始檔位置
                $outputFile = $file_path;   // 輸出位置
                $outputQuality = 50;        // 品質
                $imageLayer = imagecreatefromjpeg($sourceFile);
                imagejpeg($imageLayer, $outputFile, $outputQuality);
                $file_size = round(((filesize($path . $filename)) / 1024), 2);

                $file_ext = explode('.', $filename);
                $file_ext = end($file_ext);
                $file_uuid = Str::uuid();
                $output_path = $file_uuid . '.' . $file_ext;
                rename($file_path, $path . $output_path);
                return [
                    'image_size' => $file_size,
                    'unit' => 'kb',
                    'path' => $path,
                    'name' => $file_uuid . '.' . $file_ext,
                    'file_path' => $path . $output_path
                ];
            } else {
                return [
                    'image_size' => 0,
                    'unit' => 'kb',
                    'path' => '',
                    'name' => '',
                    'file_path' => ''
                ];
            }
        } else {
            return [
                'image_size' => 0,
                'unit' => 'kb',
                'path' => '',
                'name' => '',
                'file_path' => ''
            ];
        }
    }

    /**changeImage($path)
     * 圖片檔案轉檔成jpg，目前僅做png 轉jpeg
     */
    private function changeImage($path)
    {
        // echo $path . PHP_EOL;
        // 1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，
        // 6 = BMP，7 = TIFF(intel byte order)，8 = TIFF(motorola byte order)，
        // 9 = JPC，10 = JP2，11 = JPX，12 = JB2，13 = SWC，
        // 14 = IFF，15 = WBMP，16 = XBM
        try {
            $image_info = getimagesize($path);
            // png to jpeg
            if ($image_info[2] == 3) {
                $png = imagecreatefrompng($path);
                imagejpeg($png, $path);
            }
            // git to jpg
            if($image_info[2]==1){
                $gif = imagecreatefromgif($path);
                imagejpeg($gif, $path);
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    function is_image($path)
    {
        try {
            $a = getimagesize($path);

            $image_type = $a[2];
            if (in_array($image_type, array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP))) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
