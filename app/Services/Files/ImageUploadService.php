<?php

namespace App\Services\Files;

use App\Services\Files\FileUploadService;

use App\Models\Image;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

class ImageUploadService
{
    private $file_name, $path, $extension;
    private $image_name;
    private $image_data;
    private $type;
    private $file_push_info;
    private $image_type;
    private $date;
    private $temp_file_name;
    private $url;
    private $image_id;
    // 圖片副檔名限制
    private $extension_restrict = ["jpeg", "jpg", "png"];
    // 圖片長寬限制
    private $wh_restrict = ["1960", "1080"];
    // private $wh_restrict = ["600", "300"];

    private $fk_id;

    function __construct()
    {
    }

    /** addImage()
     *  圖片檔案上傳
     *  @param string $image_data base64
     *  @param string $type 圖片類別，error、account... ..等
     *  @param integer $fk_id 對應表格的pk_id
     *  @param string $image_name 圖片名稱
     */
    public function addImage($image_data, $type, $fk_id = 0, $image_name = null)
    {
        // 檔案儲存資料夾
        $this->path = 'img';

        $this->type = $type;
        $this->fk_id = $fk_id;
        // 圖片檔
        $temp = explode(",", $image_data);
        // 解base64
        $this->image_data = base64_decode($temp[1]);
        list($temp,) = explode(";", $temp[0]);

        // 取得副檔名
        list($temp, $extension) = explode("/", $temp);
        $this->extension = $extension;

        //寫入資料庫，取得日期pk_id
        $db_info = $this->saveInDB();
        $this->file_name = str_replace(["-", " ", ":"], "", $db_info['created_at']) . $db_info['id'];

        // 圖片名稱 
        $this->image_name = ($image_name == null || $image_name == "") ? $this->file_name : $image_name;
        // url
        $this->url = Request::server("SERVER_NAME");
        // 儲存圖片
        $this->saveInLocal();

        // 將檔名等訊息重寫回資料表
        $this->saveInDBInfo($db_info['id']);
        $this->image_id = $db_info['id'];
        return $db_info['id'];
    }

    public function updateImage($image_data, $type, $image_id, $image_name = null)
    {
        // 1 . 檢查輸入id 是否存在
        $image_info = Image::find($image_id);
        if (!$image_info) return "no data!";
        $image_old_data = Image::where('id', '=', $image_id)->first();
        // 2. 移除圖片
        // $local_path = str_replace("/", '\\', base_path() . "\\resources\\" . $image_info['path']);
        $local_path = base_path() . "/resources/" . $image_info['path'];
        // if image is exist ,delete image.
        (file_exists($local_path)) && unlink($local_path);

        $image_updata = explode(",", $image_data);
        $image_updata[1] = base64_decode($image_updata[1]);
        file_put_contents($local_path, $image_updata[1]);
        $image_info->user_id = Auth::user()->id;
        $image_info->save();

        // 移除舊webp圖檔
        $old_webp_path = str_replace($image_old_data['extension'], 'webp', $local_path);
        (file_exists($old_webp_path)) && unlink($old_webp_path);

        // image to webp
        $this->imageToWebp($local_path, $image_info->extension);
        return true;
    }

    // 新增資料置資料表，並取得資料表流水號
    public function saveInDB()
    {

        $image = Image::create([
            "image_name" => "",
            "file_name" => "",
            "path" => "",
            "url" => "",
            "user_id" => Auth::user()->id,
            "fk_id" => 0,
            "extension" => "",
            "type" => $this->type
        ]);

        // 取得流水號 + 新增日期時間

        return $image;
    }

    // 圖片訊息存入資料庫
    public function saveInDBInfo($id)
    {
        $image = Image::find($id);
        $image->image_name = $this->image_name;
        $image->file_name = $this->file_name;
        $image->path = $this->path . "/" . $this->file_name . "." . $this->extension;
        $image->url = env('API_URL') . '/api/image/get_image/' . $id;
        $image->extension = $this->extension;
        $image->type = $this->type;
        ($this->fk_id !== 0) && $image->fk_id = $this->fk_id;
        $image->save();
    }

    // 圖片儲存
    public function saveInLocal()
    {

        $service = new FileUploadService();
        $service->setFilePath($this->path);
        $service->setFileName($this->file_name);
        $service->setFileExtension($this->extension);
        $service->setFileDate($this->image_data);
        $service->save();
        $this->file_push_info = $service->getFileInfomation();
        // ----------------------------------------------------------------
        // jpeg、png、gif 轉 webp
        $this->imageToWebp($this->file_push_info['full_qualified_path'], $this->extension);
    }

    /** getImageInformation
     *  取得圖片資訊
     *  @param string|integer $image_id 圖片id
     *  @return array
     */
    public function getImageInformation($image_id)
    {
        $image_information = Image::find($image_id)->toJson();
        return json_decode($image_information, true);
    }

    public function getId()
    {
        return $this->image_id;
    }


    public function getData()
    {
        // return $this->temp_file_name.".jpg";
        return $this->file_push_info['id'] = $this->image_id;
    }

    public function getFilePath()
    {
        return $this->path;
    }

    /** checkImageExtension()
     *  副檔名格式確認，只接受 jpeg | jpg | png (true)，其餘格式 (false)
     *  @param string $image_data base64圖檔格式
     *  @return boolean
     */
    public function checkImageExtension($image_data)
    {
        // 圖片檔
        $temp = explode(",", $image_data);
        // 解base64
        $image_data = base64_decode($temp[1]);
        list($temp,) = explode(";", $temp[0]);

        // 取得副檔名
        list($temp, $extension) = explode("/", $temp);

        return in_array($extension, $this->extension_restrict);
    }

    /** checkImageWidthHeight() 
     *  檢查圖片寬高，是否超出限制，未超出限制true。
     *  @param string 
     */
    public function checkImageWidthHeight($str)
    {
        list($width, $height) = explode(",", $str);

        $width = str_replace("px", "", $width);
        $width = str_replace("width:", "", $width);

        $height = str_replace("px", "", $height);
        $height = str_replace("height:", "", $height);
        // return [$width, $height];
        if ($width > $this->wh_restrict[0]) return false;
        if ($height > $this->wh_restrict[1]) return false;

        return true;
    }

    /** setWhRestrict()
     *  設定寬高限制
     *  @param  array  [width,height]-寬高請使用整數
     *  @return voide
     */
    public function setWhRestrict($arr)
    {
        $this->wh_restrict = $arr;
    }

    /** getWh_restrict()
     *  取得圖片長、寬限制。
     *  @return array [ 寬 , 高 ]
     */
    public function getWhRestrict()
    {
        return $this->wh_restrict;
    }
    /** deleteImageFile($fk_id,$type,$action)
     *  刪除圖片資料庫資料、實體檔案。action預設為全部刪除。
     *  @param integer|string $fk_id
     *  @param string $type 資料類別
     *  @param integer $action 1.刪除資料庫資料|2.刪除實體檔案|3.刪除資料庫資料及實體檔案
     */
    public function deleteImageFile($fk_id, $image_type, $action = 3)
    {
        $image = Image::where('type', '=', $image_type)->where('fk_id', '=', $fk_id)->get();
        $del_data = function () use ($image_type, $fk_id) {
            $image = Image::where('type', '=', $image_type)->where('fk_id', '=', $fk_id)->delete();
        };

        $del_file = function () use ($image) {
            foreach ($image as $val) {
                $local_path = str_replace("/", '/', base_path() . "/storage/files/" . $val['path']);
                $local_path_webp = str_replace($val['extension'], 'webp', $local_path);
                // unlink($local_path);
                (file_exists($local_path)) && unlink($local_path);
                (file_exists($local_path_webp)) && unlink($local_path_webp);
            }
        };

        switch ($action) {
            case '1':
                $del_data();
                break;
            case '2':
                $del_file();
                break;
            case '3':
                $del_file();
                $del_data();
        }
    }
    /** imageToWebp()
     *  圖片轉檔 webp，目前僅支援jpeg、jpg、png
     * 
     */
    public function imageToWebp($source_path, $extension)
    {
        $file = $source_path;
        $to = str_replace($extension, 'webp', $file);

        switch ($extension) {
            case 'jpg':
                $image =  imagecreatefromjpeg($file);
                // ob_start();
                // imagejpeg($image, NULL, 100);
                // $cont =  ob_get_contents();
                // ob_end_clean();
                // imagedestroy($image);
                // $image =  imagecreatefromstring($cont);

                break;
            case 'jpeg':
                $image =  imagecreatefromjpeg($file);
                break;
            case 'png':
                @$image = imagecreatefrompng($file);
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);

                break;
        }
        // IMAGE_QUALITY 壓縮率
        imagewebp($image, $to, env('IMAGE_QUALITY'));
        imagedestroy($image);
    }
}
