<?php

namespace App\Services\Kkday\Booking;

use App\Services\Kkday\KkdayService;
use App\Services\Kkday\QueryPackageService;
use App\Models\Kkday\KkdayOrder;
use App\Models\Kkday\KkdayOrderCustom;

/**
 * Class OrderService.
 */
class OrderService extends KkdayService
{

    //
    public function getOrder($data){

        // kkday QueryPackageService
        $prod_params = [
            'prod_no' => $data['prod_no'],
            'pkg_no' => $data['pkg_no'],
            'locale' => 'zh-tw'
        ];

        $queryPackage = (new QueryPackageService())->index($prod_params);
        $queryPackage = json_decode($queryPackage->getContent(), true);

        // 取得 guid
        $guid = $queryPackage['data']['guid'];
        $data['guid'] = $guid;
        
        dd($data);


    }

}
