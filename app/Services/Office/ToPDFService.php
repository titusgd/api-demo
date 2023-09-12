<?php

namespace App\Services\Office;

use App\Services\Service;
// use \DOMDocument;
use Illuminate\Support\Arr;
use \PhpOffice\PhpWord\PhpWord;
use \PhpOffice\PhpWord\SimpleType\Jc;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use \PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use Dompdf\Dompdf;
use Dompdf\Options;
// use Dompdf\Dompdf;
// use setasign\Fpdi\Fpdi;
use App\Models\Office\TempTravel;

class ToPDFService extends Service
{

    // private $domain = 'https://www.*.com.tw/api/';
    // private $api_names = [
    //     [
    //         // 主檔
    //         'api' => 'travel_detail_info.asp',
    //         'api_name' => 'info',
    //         'key' => 'travel_no',
    //         'value' => ''
    //     ],
    //     [
    //         // 航班
    //         'api' => 'travel_flight.asp',
    //         'api_name' => 'flight',
    //         'key' => 'travel_no',
    //         'value' => ''
    //     ],
    //     [
    //         // 特色
    //         'api' => 'travel_detail_feature.asp',
    //         'api_name' => 'feature',
    //         'key' => 'travel_no',
    //         'value' => ''
    //     ], [
    //         // 日行程
    //         'api' => 'travel_detail_schedule.asp',
    //         'api_name' => 'schedule',
    //         'key' => 'travel_no',
    //         'value' => ''
    //     ], [
    //         // 自費
    //         'api' => 'travel_detail_expense.asp',
    //         'api_name' => 'expense',
    //         'key' => 'travel_no',
    //         'value' => ''
    //     ], [
    //         // 備註
    //         'api' => 'travel_detail_note.asp',
    //         'api_name' => 'detail_note',
    //         'key' => 'no',
    //         'value' => ''
    //     ]
    // ];
    private $api_info = [];
    private $api_data = [];
    // private $word_style = [];
    private $file_info = [];
    // private $php_word;
    // private $section;
    private $show_info;
    // private $travel_no;
    // private $employee_id;
    private $err_log;
    private $temp_travels;
    private $image_paths;
    public function __construct($travel_id, $show_info = [], $setting = true)
    {

        if ($setting) {
            $this->show_info = $show_info;
            $this->err_log = collect();
            $this->api_info = collect();
            $this->api_data = collect();
            $this->image_paths = collect();

            $temp_travels = TempTravel::select(
                'id',
                'travel_no',
                'info',
                'travel_note',
                'flight',
                'feature',
                'schedule',
                'expense',
                'detail_note',
                'sales_data',
                's_total'
            )->find($travel_id);

            $this->file_info = collect([
                'base_path' => storage_path(),
                'folder' => '/travel/temp/',
                'name' => $temp_travels->travel_no
            ]);

            $temp_travels = collect($temp_travels->toArray());
            $temp_travels->forget('id')->forget('travel_no');
            $temp_travels->map(function ($item, $key) {
                $item = (!empty($item) && $item != 'null' && $item != '') ? json_decode($item, true) : null;
                $this->api_data->put($key, $item);
            });
            $this->api_data->put('s_total', (!empty($temp_travels['s_total'])) ? $temp_travels['s_total'] : "");
            $service = new OfficeService();
            $this->api_data = $this->api_data->map(function ($item, $key) use ($service) {
                switch ($key) {
                    case 'info':
                        // $temp_src = str_replace('http://', '//', $this->api_data['info']['data'][0]['images']);
                        // $temp_src = str_replace('https://', '//', $temp_src);
                        $temp_src = str_replace(
                            ['http://', 'https://'],
                            '//',
                            $this->api_data['info']['data'][0]['images']
                        );
                        $info_data_image = $service->imageDownload($temp_src);

                        $item['data'][0]['images'] = $info_data_image['name'];
                        // dd($info_data_image, $item['data'][0]['images']);
                        if (!empty($info_data_image['file_path'])) {
                            $item['data'][0]['images'] = base64_encode(file_get_contents($info_data_image['file_path']));
                        }
                        $this->image_paths->push($info_data_image['file_path']);
                        break;
                    case 'feature':
                        foreach ($item['data'] as $key => $value) {
                            if (!empty($value['images'])) {
                                $img_arr = collect(explode(',', $value['images']));
                                $img_arr = $img_arr->filter();
                                $img_arr = $img_arr->map(function ($item) use ($service) {
                                    // 濾除 http、https
                                    // $item = str_replace('http://', '//', $item);
                                    // $item = str_replace('https://', '//', $item);
                                    $item = str_replace(['http://', 'https://'], '//', $item);

                                    $image_download = $service->imageDownload($item);
                                    // $item = $image_download['name'];
                                    if (!empty($image_download['file_path'])) {
                                        $item = base64_encode(file_get_contents($image_download['file_path']));
                                    }
                                    $this->image_paths->push($image_download['file_path']);
                                    return $item;
                                });
                                $img_arr = implode(',', $img_arr->toArray());
                                $item['data'][$key]['images'] = $img_arr;
                            }
                        }
                        break;
                    case 'schedule':
                        foreach ($item['data'] as $key => $value) {
                            foreach ($value['view'] as $key2 => $value2) {
                                // $img_src = str_replace('http://', '//', $value2['images']);
                                // $img_src = str_replace('https://', '//', $img_src);
                                $img_src = str_replace(
                                    ['http://', 'https://'],
                                    '//',
                                    $value2['images']
                                );

                                $image_download = $service->imageDownload($img_src);
                                if (!empty($image_download['file_path'])) {
                                    $item['data'][$key]['view'][$key2]['images'] = base64_encode(file_get_contents($image_download['file_path']));
                                }
                                $this->image_paths->push($image_download['file_path']);
                            }
                        }
                        break;
                }
                return $item;
            });
            // dd($this->api_data['schedule']);
        } else {
            $temp_travels = TempTravel::select(
                'id',
                'travel_no'
            )->find($travel_id);
            $this->temp_travels = $temp_travels;
        }
    }

    public function create()
    {
        if (!empty($this->err_log->toArray())) return $this->err_log->toArray();
        $data = collect();
        // 出團名稱
        if ($this->show_info['travel_name']) {
            $data->put('travel_name', (!empty($this->api_data['s_total'])) ? $this->api_data['s_total'] : '喜　鴻　假　期');
        }
        // 主題名稱
        if ($this->show_info['travel_slogan']) {
            $temp_arr = collect();
            if (!empty($this->api_data['info']['data'][0]['slogan'])) {
                $temp_arr->put('title', $this->api_data['info']['data'][0]['slogan']);
            }
            $temp_arr->put('content', (!empty($this->api_data['info']['data'][0]['title_2'])) ? $this->brFormat($this->api_data['info']['data'][0]['title_2']) : '');
            $data->put('slogan', $temp_arr->toArray());
        }
        // 主題附註
        if ($this->show_info['travel_slogan_note']) {
            if (!empty($this->api_data['info']['data'][0]['title_3'])) {
                $data->put('slogan_note', $this->brFormat($this->api_data['info']['data'][0]['title_3']));
            }
        }
        // memo 警語
        if (!empty($this->api_data['info']['data'][0]['memo'])) {

            $temp_style = [];
            if (strpos($this->api_data['info']['data'][0]['memo'], "red") !== false) {
                $temp_style['color'] = "#ff0000";
            }
            if (strpos($this->api_data['info']['data'][0]['memo'], "Red") !== false) {
                $temp_style['color'] = "#ff0000";
            }
            if (strpos($this->api_data['info']['data'][0]['memo'], "blue") !== false) {
                $temp_style['color'] = "#0000FF";
            }
            if (strpos($this->api_data['info']['data'][0]['memo'], "Blue") !== false) {
                $temp_style['color'] = "#0000FF";
            }
            $temp_str = strip_tags($this->api_data['info']['data'][0]['memo']);

            $data->put('travel_memo', [
                'text' => $temp_str,
                'color' => (!empty($temp_style['color'])) ? $temp_style['color'] : ''
            ]);
        }
        // icon
        if (!empty($this->api_data['info']['data'][0]['promotions'])) {

            $icons = collect();
            $icons->put("A", [
                "url" => "detail_page_icon03.jpg",
                "text" => "無購物站",
            ])->put("B", [
                "url" => "detail_page_icon04.jpg",
                "text" => "無自費",
            ])->put("C", [
                "url" => "detail_page_icon05.jpg",
                "text" => "送小費",
            ])->put("D", [
                "url" => "detail_page_icon06.jpg",
                "text" => "保證出團",
            ])->put("E", [
                "url" => "detail_page_icon07.jpg",
                "text" => "聯營團體",
            ])->put("F", [
                "url" => "detail_page_icon08.jpg",
                "text" => "車上WIFI",
            ])->put("G", [
                "url" => "detail_page_icon09.jpg",
                "text" => "商務三排車",
            ])->put("H", [
                "url" => "detail_page_icon10.jpg",
                "text" => "免費WIFI",
            ])->put("K", [
                "url" => "detail_page_icon13.jpg",
                "text" => "熊本熊束帶",
            ])->put("L", [
                "url" => "detail_page_icon14.jpg",
                "text" => "雪鞋套",
            ])->put("M", [
                "url" => "detail_page_icon15.jpg",
                "text" => "導覽耳機",
            ])->put("N", [
                "url" => "detail_page_icon17.jpg",
                "text" => "無車購",
            ])->put("O", [
                "url" => "detail_page_icon19.jpg",
                "text" => "環保袋",
            ])->put("P", [
                "url" => "detail_page_icon20.jpg",
                "text" => "旅行六件組",
            ])->put("Q", [
                "url" => "detail_page_icon21.jpg",
                "text" => "彩色束帶",
            ])->put("R", [
                "url" => "detail_page_icon22.jpg",
                "text" => "新春紅包袋",
            ])->put("S", [
                "url" => "detail_page_icon23.jpg",
                "text" => "上網SIM卡",
            ])->put("T", [
                "url" => "detail_page_icon24.jpg",
                "text" => "中國打卡機",
            ])->put("U", [
                "url" => "detail_page_icon25.jpg",
                "text" => "行動電源",
            ])->put("V", [
                "url" => "detail_page_icon27.jpg",
                "text" => "防水袋",
            ])->put("W", [
                "url" => "detail_page_icon28.jpg",
                "text" => "有位有房",
            ])->put("X", [
                "url" => "detail_page_icon29.jpg",
                "text" => "免費SIM卡",
            ])->put("Y", [
                "url" => "detail_page_icon30.jpg",
                "text" => "沐浴旅行組",
            ])->put("Z", [
                "url" => "detail_page_icon12.jpg",
                "text" => "熊本熊行李牌",
            ])->put("I", [
                "url" => "detail_page_icon31.jpg",
                "text" => "小孩不佔床半價",
            ]);

            $promotions = collect(str_split($this->api_data['info']['data'][0]['promotions']));
            $promotions->map(function ($code, $key) use ($icons, &$promotions) {
                if (is_numeric($code)) {
                    $promotions->forget($key);
                } else {
                    $promotions[$key] = $icons[$code]['url'];
                }
            });
            // image to base64
            $promotions = $promotions->map(function ($item) {
                $image_path = base_path() . '/storage/travel/imgs/promotions/' . $item;
                $item = base64_encode(file_get_contents($image_path));
                return $item;
            });
            $data->put('icons', $promotions->toArray());
        }
        // 出發日期
        $data->put('departure', $this->api_data['info']['data'][0]['date']);

        // 優惠 discount
        if ($this->show_info['discount']) {
            if (!empty($this->api_data['info']['data'][0]['discount'])) {
                $str = collect();
                foreach ($this->api_data['info']['data'][0]['discount'] as $discount) {
                    $temp_str = '';
                    $temp_str .= (!empty($discount['name'])) ? '【' . $discount['name'] . '】' : '';
                    $temp_str .= (!empty($discount['value'])) ? "省 {$discount['value']}" : '';
                    if ((!empty($discount['amounts'])) && (!empty($discount['amounts_1']))) {
                        $temp_str .= "限 {$discount['amounts']} (剩 {$discount['amounts_1']} 名)";
                    }
                    $str->push($temp_str);
                }
                $data->put('discount', $str->toArray());
            }
        }
        // 行程特色
        // if ($this->show_info['schedule_feature']) {
        if (!empty($this->api_data['feature']['data'])) {
            $temp_arr = collect();
            foreach ($this->api_data['feature']['data'] as $item) {

                $item_arr = array(
                    'title' => (!empty($item['name'])) ? $item['name'] : '',
                    'content' => (!empty($item['content'])) ? $this->brFormat($item['content']) : '',
                    'images' => ''
                );
                if (!empty($item['images'])) {
                    $image_urls = explode(',', $item['images']);
                    $image_urls = array_filter($image_urls);
                    $item_arr['images'] = $image_urls;
                }
                $temp_arr->push($item_arr);
            }

            // 費用說明
            $temp_arr->push([
                'title' => '費用說明',
                'content' => (!empty($this->api_data['info']['data'][0]['price_memo'])) ? $this->brFormat($this->api_data['info']['data'][0]['price_memo']) : '',
                'images' => ''
            ]);

            $data->put('feature', $temp_arr->toArray());
        }

        // 日行程
        //schedule_content 行程內容
        $flight_data = collect($this->api_data['flight']['data']);
        $flight_data = $flight_data->map(function ($item) {
            $temp_arr = [];
            if (strstr($item['time_2'], '+')) {
                list(, $temp_num) = explode('+', $item['time_2']);
                $temp_arr['num'] = $temp_num;
            } else {
                $temp_arr['num'] = 0;
            }
            $temp_arr['text'] = preg_replace("/[^a-zA-Z]/", "", $item['place_1'])
                . '/'
                . preg_replace("/[^a-zA-Z]/", "", $item['place_2']) . ' '
                . $item['flight'] . ' '
                . $item['time_1'] . '~' . $item['time_2'];
            return $temp_arr;
        });
        $first_day = !empty($flight_data[0]) ? $flight_data[0] : 0;
        $total_day = count($this->api_data['schedule']['data']);
        $end_day = ($first_day != 0) ? $flight_data[count($flight_data) - 1] : 0;
        $show_day = ($end_day) ? $total_day - $end_day['num'] : 0;

        $temp = collect();
        foreach ($this->api_data['schedule']['data'] as $schedule) {
            if(empty($schedule['hotel']['data'])) {
                $schedule['hotel']['data']=[];
            }
            // try {
                $temp_schedule = [
                    'day' => $schedule['day'],
                    'title' => $this->brFormat($schedule['abstract_1']),
                    'hotel' => $schedule['hotel']['data']
                ];
            // } catch (\Exception $e) {
            //     dd($schedule['hotel']['data'], $schedule['day']);
            // }
            $temp_schedule['flight'] = '';
            if ($schedule['day'] == 1) {
                if (!empty($first_day['text'])) {
                    $temp_schedule['flight'] = $first_day['text'];
                }
            }
            if ($schedule['day'] == $show_day) {
                $temp_schedule['flight'] = $end_day['text'];
            }

            // 內容
            if ($this->show_info['schedule_content']) {
                if (!empty($schedule['memo_1'])) {
                    $temp_schedule['content'] = $this->brFormat($schedule['memo_1']);
                }
            }
            // 溫馨提醒
            if ($this->show_info['schedule_kind_reminder']) {
                $temp_schedule['schedule_kind_reminder'] = $this->brFormat($schedule['memo_3']);
            }
            // 日行程特別說明
            if ($this->show_info['schedule_special_note']) {
                $temp_schedule['schedule_special_note'] = $this->brFormat($schedule['memo_2']);
            }
            // 餐食
            if ($this->show_info['schedule_serve']) {
                $temp_schedule['schedule_serve'] = html_entity_decode("早餐：{$schedule['breakfast']}，中餐：{$schedule['lunch']}，晚餐：{$schedule['dinner']}");
            }
            // 景點介紹 + 景點圖片
            $introduce = collect();
            foreach ($schedule['view'] as $view) {
                $introduce->push([
                    'title' => $view['name'],
                    'content' => $this->brFormat($view['memo_2']),
                    'img' => (!empty($view['images'])) ? ($view['images']) : '',
                    'note' => $this->brFormat($view['memo_3'])    // 特別備註
                ]);
            }
            $temp_schedule['introduceImg'] = $introduce->toArray();
            $temp->push($temp_schedule);
        }
        $data->put('schedule', $temp->toArray());
        // 注意事項
        if ($this->show_info['please_note']) {
            $temp_note = collect($this->api_data['detail_note']);
            $temp_note = $temp_note->map(function ($item, $key) {
                if ($key != 0 & (empty($item['error_log']))) {
                    $temp = collect();
                    $temp->put('title', $item['title']);
                    $temp->put('data', collect());
                    foreach ($item['data'] as $content) {
                        $temp['data']->push([
                            'type' => $content['type'],
                            'text' => $this->brFormat($content['text']),
                            'link' => $content['link']
                        ]);
                    }
                    return $temp->toArray();
                }
            });
            $data->put('please_note', $temp_note->filter()->toArray());
        }
        // 自費推薦
        if (!empty($this->api_data['expense'])) {
            $data->put('expense', $this->api_data['expense']['data']);
        }
        // 航班資訊
        $data->put('flight', $this->api_data['flight']);
        // 旅遊注意事項
        if ($this->show_info['travel_please_note']) {
            $temp = collect();
            foreach ($this->api_data['travel_note']['data'] as $note) {
                // dd($note);
                $temp->push([
                    'title' => $note['title'],
                    'content' => (!empty($this->brFormat($note['content'])) ? $this->brFormat($note['content']) : [])
                ]);
            }
            $data->put('travel_please_note', [
                'country' => $this->api_data['travel_note']['country'],
                'data' => $temp->toArray()
            ]);
        }
        // 團費參考
        if ($this->show_info['price']) {
            $temp_arr = collect($this->api_data['info']['data'][0]['price'])->chunk(3);
            $data->put('price', $temp_arr);
        }
        // 業務員
        if ($this->show_info['sales_data']) {
            // dd($this->api_data['sales_data']);
            if ($this->api_data['sales_data']['status'] == 0 || $this->api_data['sales_data']['status'] == "0") {
                $temp = collect($this->api_data['sales_data']['data'][0]);
                $temp->put('company_url', 'http://www.*.com.tw/');
                $data->put('sales_data', $temp->toArray());
            } else {
                $this->err_log->push([
                    'status' => $this->api_data['sales_data']['status'],
                    'message' => $this->api_data['sales_data']['message']
                ]);
            }
        }
        $data->put('test_text', ['<span style="font-family:emoji;">&#9992</span>', '&#9992', '']);
        // --------------------必定產生 -----
        $options = new Options();
        // PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('reports.invoiceSell')->stream();
        // $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        // $options->set('defaultFont','emoji');
        $dompdf = new Dompdf();
        $html = view('pdf.application', ['show_info' => $this->show_info, 'data' => $data]);
        // echo $html;

        // dd('eeee');
        $dompdf->loadHtml($html, "UTF-8");
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->setOptions($options);
        // $dompdf->setOptions('defaultFont','emoji');
        $dompdf->render();
        // $dompdf->html_render($html);

        $output = $dompdf->output();

        file_put_contents(
            $this->file_info['base_path']
                . $this->file_info['folder']
                . $this->file_info['name'] . '.pdf',
            $output
        );
        // 清除圖片
        $this->image_paths->map(function ($item) {
            if (!empty($item)) {
                unlink($item);
            }
        });
        return [$this->file_info['base_path'] . $this->file_info['folder'] . $this->file_info['name'] . '.pdf', $this->file_info['name']];
    }

    public function brFormat($str)
    {
        // br 格式化
        $pattern = '/<[^>]*(br)[^>]*>/im';
        $replacement = "<br/>";
        $str = preg_replace($pattern, $replacement, $str);

        $str = str_replace('target="_blank"', '', $str);

        // 濾除&#XXXX;
        // $pattern = '/\&\#[0-9]{4};/';
        // $replacement = '';
        // $str = preg_replace($pattern, $replacement, $str);

        $pattern = '/<[^>]*(font|font color)[^>]*>/im';
        $replacement = '';
        $str = preg_replace($pattern, $replacement, $str);


        $str = explode('<br/>', $str);
        $str = collect($str);
        $str = $str->map(function ($item) {
            $temp_str = $item;
            // 9986-10160
            // 128513-128591
            preg_match_all("/&#(.*?);/", $item, $data);
            $data = array_filter($data);
            if (!empty($data)) {
                foreach ($data[0] as $key => $val) {
                    $mce = mb_convert_encoding($val, 'UTF-8', 'HTML-ENTITIES');
                    $temp_str = str_replace($val, $mce, $temp_str);
                }
            } else {
                $temp_str = $item;
            }
            return $temp_str;
        });
        // 濾除例外
        $str->map(function ($item, $key) use ($str) {
            (strpos($item, 'iframe width')) && $str->forget($key);
        });
        return $str->toArray();
    }
    public function downloadPath()
    {
        return [
            'file_path' => storage_path('travel/temp/') . $this->temp_travels['travel_no'] . '.pdf',
            'file_name' => $this->temp_travels['travel_no'] . '.pdf'
        ];
    }
}
