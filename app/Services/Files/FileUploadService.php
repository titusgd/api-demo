<?php

namespace App\Services\Files;

class FileUploadService
{
    private $file_path, $file_name, $file_extension, $file_size;
    private $file_type;
    private $base_path;
    private $data;

    function __construct()
    {
        // 基底實體路徑 
        // $this->base_path = base_path() . "/resources";
        $this->base_path = base_path() . "/storage/files";
    }

    public function save()
    {
        // 路徑檢查
        // 基底路徑
        if (!isset($this->base_path)) return "no base_path!";
        // 檔案路徑
        if (!isset($this->file_path))  return "no file_path!";
        // 檔案名稱
        if (!isset($this->file_path)) return "no file_path!";
        // 副檔名
        if (!isset($this->file_extension)) return "no file_extension!";
        $path = $this->base_path . "/" . $this->file_path . "/" . $this->file_name . "." . $this->file_extension;
        // 檔案儲存
        file_put_contents($path, $this->data);
        $this->file_size = floor((filesize($path)) / 1024);
        // 回傳新增檔案相關訊息
        return $this->getFileInfomation();
    }

    //  -------------------------------- set、get --------------------------------

    public function getFileInfomation()
    {
        return [
            "base_path" => $this->base_path,
            "file_path" => $this->file_path,
            "file_name" => $this->file_name,
            "file_extension" => $this->file_extension,
            "file_size" => $this->file_size . " kb",
            "full_qualified_path" => $this->base_path . "/" . $this->file_path . '/' . $this->file_name . "." . $this->file_extension
        ];
    }


    // 儲存路徑
    public function setFilePath($file_path)
    {
        $this->file_path = $file_path;
    }
    public function getFilePath()
    {
        return $this->file_path;
    }
    // 取得檔案路徑
    public function getFileBasePath()
    {
        return $this->base_path;
    }
    // 檔名設定
    public function setFileName($file_name)
    {
        $this->file_name = $file_name;
    }
    // 取得檔案名稱
    public function getFileName()
    {
        return $this->file_name;
    }

    // file_extension 副檔名
    public   function setFileExtension($file_extension)
    {
        $this->file_extension = $file_extension;
    }

    // file_extension 取得副檔名
    public function getFileExtension()
    {
        return $this->file_extension;
    }

    // setFileDate 檔案資料
    public function setFileDate($data)
    {
        $this->data = $data;
    }

    public function getFileSize()
    {
        return $this->file_size;
    }
}
