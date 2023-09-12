<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Models\Ticket\TicketProductBase;

class KkdayProductBaseCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'KkdayProductBaseCheck';


    protected $description = 'kkday product check';


    public function handle()
    {

        $res = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->KkdayLogin(),
        ])->get(
            'https://api-dev.*.com.tw/api/kkday/product_search',
            [
                'page' => 1,
                'page_size' => 1000,
            ]
        );

        $page_count = $res->json()['page']['total'];
        for ($vl = 1; $vl <= $page_count; $vl++) {
            $res = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->KkdayLogin(),
            ])->get(
                'https://api-dev.*.com.tw/api/kkday/product_search',
                [
                    'page' => $vl,
                    'page_size' => 1000,
                ]
            );

            $data = $res->json()['data']['prods'];

            Log::info('=== KkdayProductBase start ===');
            foreach ($data as $k => $v) {

                $prod_no = $v['prod_no'];
                $base = TicketProductBase::where('prod_no', $prod_no)->first();
                if (!$base) {
                    Log::info('=== KkdayProductBase insert prod_no:' . $prod_no . ' ===');
                    $productBase = new TicketProductBase();
                    $productBase->prod_no = $v['prod_no'];
                    $productBase->prod_name = $v['prod_name'];
                    $productBase->prod_url_no = $v['prod_url_no'];
                    $productBase->prod_type = $v['prod_type'];
                    $productBase->tag = json_encode($v['tag']);
                    $productBase->rating_count = $v['rating_count'];
                    $productBase->avg_rating_star = $v['avg_rating_star'];
                    $productBase->instant_booking = $v['instant_booking'];
                    $productBase->order_count = $v['order_count'];
                    $productBase->days = $v['days'];
                    $productBase->hours = $v['hours'];
                    $productBase->duration = $v['duration'];
                    $productBase->introduction = $v['introduction'];
                    $productBase->prod_img_url = $v['prod_img_url'];
                    $productBase->b2c_price = $v['b2c_price'];
                    $productBase->b2b_price = $v['b2b_price'];
                    $productBase->prod_currency = $v['prod_currency'];
                    $productBase->countries = json_encode($v['countries']);
                    $productBase->purchase_type = $v['purchase_type'];
                    $productBase->purchase_date = $v['purchase_date'];
                    $productBase->earliest_sale_date = $v['earliest_sale_date'];
                    $productBase->save();
                }
            }
            Log::info('=== KkdayProductBase end ===');
        }
    }

    public function KkdayLogin()
    {

        $url = 'https://api-dev.*.com.tw/api/auth/login';

        $response = Http::post($url, [
            'id' => '08116',
            'pw' => 'hezrid5661',
        ]);

        $token = $response->json()['data']['token'];

        return $token;
    }
}
