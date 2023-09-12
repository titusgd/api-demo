<?php

namespace App\Services\Kkday;

use App\Services\Kkday\KkdayService;

/**
 * Class SearchService.
 */
class QueryPackageService extends KkdayService
{

    public $validationRules = [
        'prod_no' => 'numeric|required',
        'pkg_no' => 'numeric|required',
        's_date' => 'string|required',

    ];

    public $validationMsg = [
        'prod_no.number' => '01 prod_no',
        'prod_no.required' => '01 prod_no',
        'pkg_no.numeric' => '01 pkg_no',
        'pkg_no.required' => '01 pkg_no',
        's_date.string' => '01 s_date',
        's_date.required' => '01 s_date',
    ];

    public function index($req)
    {

        $req['state'] = $req['state'] ?? 'tw';
        $req['locale'] = $req['locale'] ?? 'zh-tw';

        $prod_params = [
            'prod_no' => $req['prod_no'],
            'locale' => 'zh-tw'
        ];


        $prod = $this->setParams($prod_params)->callApi('post', 'v3/Product/QueryProduct')->getBody();

        $data = $this->setParams($req)->callApi('post', 'v3/Product/QueryPackage')->getBody();

        $result = $data;

        $result['is_cancel_free'] = $prod['prod']['is_cancel_free'];

        // $result['prod_no'] = $req['prod_no'];
        // $result['pkg_no'] = $data['pkg_no'];
        // $result['guid'] = $data['guid'];
        // $result['is_cancel_free'] = $prod['prod']['is_cancel_free'];
        // foreach($data['item'] as $key => $iv){
        //     $result['item'][$key] = [
        //         'item_no' => $iv['item_no'],
        //         'skus' => $iv['skus']
        //     ];
        // }

        return KkdayService::response('00', 'OK', $result);
    }
}
