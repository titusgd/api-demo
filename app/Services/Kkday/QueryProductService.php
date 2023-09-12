<?php

namespace App\Services\Kkday;

use App\Services\Kkday\KkdayService;

/**
 * Class SearchService.
 */
class QueryProductService extends KkdayService
{

    public $validationRules = [
        'prod_no' => 'numeric|required',
        'state' => 'string',
        'locale' => 'string',
    ];

    public $validationMsg = [
        'prod_no.numeric' => '01 prod_no',
        'prod_no.required' => '01 prod_no',
        'state.string' => '01 state',
        'locale.string' => '01 locale',
    ];

    public function index($req){

        $req['state'] = $req['state'] ?? 'tw';

        $data = $this->setParams($req)->callApi('post', 'v3/Product/QueryProduct')->getBody();

        return KkdayService::response('00', 'OK', $data);

    }

}
