<?php

namespace App\Services\Kkday;

use App\Services\Service;
use App\Traits\KkdayTrait;

/**
 * Class KkdayService.
 */
class KkdayService extends Service
{
    use KkdayTrait;

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * loadJsonData(string $filePath, bool $toArray = true)
     * 讀取json檔案
     */
    public function loadJsonData(string $filePath, bool $toArray = true):mixed
    {
        $json = file_get_contents($filePath);
        $data = json_decode($json, $toArray);
        return $data;
    }
}
