<?php

namespace App\Services\Kkday;

use Illuminate\Support\Facades\Http;
use App\Services\Service;
use App\Models\Kkday\KkdayProduct;
use App\Models\Kkday\KkdayProductDetail;
use App\Models\Kkday\KkdayProductTag;

/**
 * Class ImportDataService.
 */
class ImportDataService extends Service
{

    public function importData()
    {

        $prodSearchUrl = 'https://api-b2d.sit.kkday.com/v3/Search';

        $prod_cond = [
            'location' => 'zh-tw',
            'state' => 'tw',
            'page_size' =>  1,
            'start' => 0
        ];

        $prods_page = $this->curl_work($prodSearchUrl, $prod_cond);

        $prods_total_count = json_decode($prods_page, true);
        $prods_total_count = $prods_total_count['metadata']['total_count'];

        $page_count = ceil($prods_total_count / 1000);

        for ($i = 1; $i <= $page_count; $i++) {

            $start = ($i - 1) * 1000;

            // 寫入 KkdayProduct
            $prod_cond = [
                'location' => 'zh-tw',
                'state' => 'tw',
                'page_size' => 1000,
                'start' => $start
            ];

            $prods = $this->curl_work($prodSearchUrl, $prod_cond);

            $prods_array = json_decode($prods, true);

            $prodArray = $prods_array['prods'];

            foreach ($prodArray as $value) {

                $this->makeProduct($value);

                $this->makeDetail($value);
            }
        }



        // dd($prodArray);
    }

    // curl post
    function curl_work($url = "", $parameter = [])
    {

        $token = 'eyJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobWFjLXNoYTI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJodHRwczovL2IyZC5ra2RheS5jb20iLCJjbGllbnQiOiJLS0RBWV9CMkQiLCJlbWFpbCI6ImR1bmdsdW5nMTY4QGJlc3R0b3VyLmNvbS50dyIsImFjY291bnRfeGlkIjoiNTMyIiwiZXhwIjoiMDMvMDcvMjAyNCAwMDo1MzozMSJ9.-E4KorA7tN-RZ1FLY_4xgByrvkU7mrKg6Ep2zZNzdoU';

        $response = Http::withHeaders(
            [
                'Authorization' => 'Bearer ' . $token,

            ]
        )->post($url, $parameter);

        $result = $response->body();

        return $result;
    }

    /**
     * make product
     */
    public function makeProduct($value)
    {
        // 寫入 products
        $prod = new KkdayProduct();
        $prod->prod_no = $value['prod_no'];
        $prod->prod_url_no = $value['prod_url_no'];
        $prod->prod_name = $value['prod_name'];
        $prod->prod_type = $value['prod_type'];
        $prod->rating_count = $value['rating_count'];
        $prod->avg_rating_star = $value['avg_rating_star'];
        $prod->instant_booking = $value['instant_booking'];
        $prod->order_count = $value['order_count'];
        $prod->days = $value['days'];
        $prod->hours = $value['hours'];
        $prod->duration = $value['duration'];
        $prod->introduction = $value['introduction'];
        $prod->prod_img_url = $value['prod_img_url'];
        $prod->b2c_price = $value['b2c_price'];
        $prod->b2b_price = $value['b2b_price'];
        $prod->prod_currency = $value['prod_currency'];
        $prod->purchase_type = $value['purchase_type'];
        $prod->purchase_date = $value['purchase_date'];
        $prod->earliest_sale_date = $value['earliest_sale_date'];
        $prod->status = 1;
        $prod->save();

        // 寫入 KkdayTag
        foreach ($value['tag'] as $tag_value) {
            $tag = new KkdayProductTag();
            $tag->prod_no = $value['prod_no'];
            $tag->tags = $tag_value;
            $tag->save();
        }
    }

    /**
     * make product detail
     */
    public function makeDetail($value)
    {
        // product detail
        $prodDetailURL = 'https://api-b2d.sit.kkday.com/v3/Product/QueryProduct';
        $prod_detail_cond = [
            'location' => 'zh-tw',
            'prod_no' => $value['prod_no'],
        ];

        $prod_details = $this->curl_work($prodDetailURL, $prod_detail_cond);

        $prod_details_array = json_decode($prod_details, true);

        if (!empty($prod_details_array['prod'])) {
            $prod_detail = $prod_details_array['prod'];

            $prodDetail = new KkdayProductDetail();
            $prodDetail->prod_no = $value['prod_no'];
            $prodDetail->is_tour = $prod_detail['is_tour'];
            $prodDetail->is_cancel_free = $prod_detail['is_cancel_free'];
            $prodDetail->timezone = $prod_detail['timezone'];
            $prodDetail->confirm_order_time = $prod_detail['confirm_order_time'];
            $prodDetail->is_translate_complete = $prod_detail['is_translate_complete'];
            $prodDetail->have_translate = $prod_detail['have_translate'];
            $prodDetail->inquiry_locale = $prod_detail['inquiry_locale'];
            $prodDetail->is_all_sold_out = $prod_detail['is_all_sold_out'];
            $prodDetail->b2c_min_price = $prod_detail['b2c_min_price'];
            $prodDetail->b2b_min_price = $prod_detail['b2b_min_price'];
            $prodDetail->avg_rating_star = $prod_detail['avg_rating_star'];
            $prodDetail->instant_booking = $prod_detail['instant_booking'];
            $prodDetail->order_count = $prod_detail['order_count'];
            $prodDetail->days = $prod_detail['days'];
            $prodDetail->prod_type = $prod_detail['prod_type'];
            $prodDetail->hours = $prod_detail['hours'];
            $prodDetail->duration = $prod_detail['duration'];
            $prodDetail->introduction = $prod_detail['introduction'];
            $prodDetail->b2c_price = $prod_detail['b2c_price'];
            $prodDetail->b2b_price = $prod_detail['b2b_price'];
            $prodDetail->prod_currency = $prod_detail['prod_currency'];
            $prodDetail->item_no = 0;
            $prodDetail->pkg_no = 0;
            $prodDetail->pkg_name = "";
            if (!empty($prod_details_array['pkg'][0])) {
                $prod_pkg = $prod_details_array['pkg'][0];
                $prodDetail->item_no = $prod_pkg['item_no'][0];
                $prodDetail->pkg_no = $prod_pkg['pkg_no'];
                $prodDetail->pkg_name = $prod_pkg['pkg_name'];
            }
            $prodDetail->save();
        }
    }
}
