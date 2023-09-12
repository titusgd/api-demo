<?php

namespace App\Services\Kkday;

use App\Services\Kkday\KkdayService;

/**
 * Class SearchService.
 */
class ProductSearchService extends KkdayService
{

    public $validationRules = [
        'instant_booking' => 'boolean',
        'country_keys.*' => 'string',
        'city_keys.*' => 'string',
        'cat_keys.*' => 'string',
        'page_size' => 'numeric',
        'date_from' => 'string',
        'date_to' => 'string',
        'guide_langs.*' => 'string',
        'price_from' => 'double',
        'price_to' => 'double',
        'keywords' => 'string',
        'sort' => 'string',
        'start' => 'string',
        'durations.*' => 'string',
        'stats.*' => 'string',
        'facets.*' => 'string',
        'has_pkg' => 'boolean',
        'tourism' => 'string',
        'have_translate' => 'boolean',
        'page' => 'numeric',
    ];

    public $validationMsg = [
        'instant_booking.boolean' => '01 instant_booking',
        'country_keys.*.string' => '01 country_keys',
        'city_keys.*.string' => '01 city_keys',
        'cat_keys.*.string' => '01 cat_keys',
        'page_size.numeric' => '01 page_size',
        'date_from.string' => '01 date_from',
        'date_to.string' => '01 date_to',
        'guide_langs.*.string' => '01 guide_langs',
        'price_from.double' => '01 price_from',
        'price_to.double' => '01 price_to',
        'keywords.string' => '01 keywords',
        'sort.double' => '01 sort',
        'start.double' => '01 start',
        'durations.*.double' => '01 durations',
        'stats.*.double' => '01 stats',
        'facets.*.double' => '01 facets',
        'has_pkg.boolean' => '01 has_pkg',
        'tourism.double' => '01 tourism',
        'have_translate.boolean' => '01 have_translate',
        'page.numeric' => '01 page',
    ];

    public function index($req)
    {

        $req['locale'] = $req['locale'] ?? 'zh-tw';
        $req['state'] = $req['state'] ?? 'tw';

        $req['page_size'] = $req['page_size'] ?? 10;
        if (!empty($req['page'])) {
            if ($req['page'] > 1) {
                $req['start'] = ($req['page'] - 1) * $req['page_size'];
            }
        }

        $data = $this->setParams($req)->callApi('post', 'v3/Search')->getBody();

        $pageInfo = [
            'total' => ceil($data['metadata']['total_count'] / $req['page_size']),
            'count_total' => $data['metadata']['total_count'],
            'page' => $req['page'] ?? 1,
        ];

        return KkdayService::response_paginate('00', 'OK', $data, $pageInfo);
    }
}
