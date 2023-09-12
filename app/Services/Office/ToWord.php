<?php

namespace App\Services\Office;

use App\Services\Service;
// use \DOMDocument;
use Illuminate\Support\Arr;
use \PhpOffice\PhpWord\PhpWord;
use \PhpOffice\PhpWord\SimpleType\Jc;
// use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use \PhpOffice\PhpWord\Shared\Html;
// use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
// use \Dompdf\Dompdf;
// use \Dompdf\Options;
use App\Services\Office\OfficeService;
use App\Models\Office\TempTravel;
// use Illuminate\Support\Facades\Log;

class ToWord extends Service
{
    private $api_info = [];
    private $api_data = [];
    private $word_style = [];
    private $file_info = [];
    private $php_word;
    private $section;
    private $employee_id;
    private $err_log;
    private $temp_travels;
    private $image_paths;
    private $execution_time;
    private $image_url;
    public function __construct($travel_id, $show_info, $setting = true)
    {
        $service = new OfficeService();
        $service->setExecuteStart('init');
        if ($setting) {
            // init
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
                'path' => 'travel/temp/',
                'name' => $temp_travels->travel_no
            ]);
            $temp_travels = collect($temp_travels->toArray());
            $temp_travels->forget('id')->forget('travel_no');
            $temp_travels->map(function ($item, $key) {
                $item = (!empty($item) && $item != 'null' && $item != '') ? json_decode($item, true) : null;
                $this->api_data->put($key, $item);
            });
            $this->api_data->put('s_total', (!empty($temp_travels['s_total'])) ? $temp_travels['s_total'] : "");
            $this->image_url['info'][0] = $this->api_data['info']['data'][0]['images'];

            // $service = new OfficeService();
            if ($show_info->get('schedule_attraction_image') || $show_info->get('schedule_feature_image')) {
                $this->api_data = $this->api_data->map(function ($item, $key) use ($service) {
                    switch ($key) {
                        case 'info':
                            $temp_src = str_replace('http://', '//', $this->api_data['info']['data'][0]['images']);
                            $temp_src = str_replace('https://', '//', $temp_src);
                            $info_data_image = $service->imageDownload($temp_src);

                            $item['data'][0]['images'] = $info_data_image['file_path'];
                            $this->image_paths->push($info_data_image['file_path']);
                            // dd($item['data'],$this->image_paths->toArray());
                            break;
                        case 'feature':
                            foreach ($item['data'] as $key => $value) {
                                if (!empty($value['images'])) {

                                    $img_arr = collect(explode(',', $value['images']));
                                    $img_arr = $img_arr->filter();
                                    $img_arr = $img_arr->map(function ($item) use ($service) {
                                        // 濾除 http、https
                                        $item = str_replace('http://', '//', $item);
                                        $item = str_replace('https://', '//', $item);

                                        $image_download = $service->imageDownload($item);
                                        $item = $image_download['file_path'];
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
                                    $img_src = str_replace('http://', '//', $value2['images']);
                                    $img_src = str_replace('https://', '//', $img_src);
                                    $image_download = $service->imageDownload($img_src);
                                    $item['data'][$key]['view'][$key2]['images'] = $image_download['file_path'];
                                    $this->image_paths->push($image_download['file_path']);
                                }
                            }
                            break;
                    }
                    return $item;
                });
            }
            // dd($this->api_data['feature'],$this->image_paths->toArray());
            $domPdfPath = base_path('vendor/dompdf/dompdf');
            \PhpOffice\PhpWord\Settings::setPdfRendererPath($domPdfPath);
            \PhpOffice\PhpWord\Settings::setPdfRendererName('DomPDF');
            \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
            // 標題預設
            $this->word_style = collect();
            // 標題 預設
            $this->word_style->put('title', collect([
                [
                    'depth' => 1,
                    'fontStyle' => ['size' => '24', 'bold' => 'true'],
                    'paragraphStyle' => ['align' => 'center']
                ],
                [
                    'depth' => 2,
                    'fontStyle' => ['color' => 'ff0000', 'size' => '24'],
                    'paragraphStyle' => []
                ],
                [
                    'depth' => 3,
                    'fontStyle' => ['size' => '24'],
                    'paragraphStyle' => []
                ],
                [
                    'depth' => 4,
                    'fontStyle' => ['color' => 'ff0000', 'size' => '18'],
                    'paragraphStyle' => ['align' => 'center']
                ], [
                    'depth' => 5,
                    'fontStyle' => ['color' => 'ff0000', 'size' => '18'],
                    'paragraphStyle' => []
                ]
            ]))
                // 文字預設
                ->put(
                    'font_default',
                    collect([
                        // 'name' => 'Microsoft JhengHei',
                        'name' => 'PMingLiU',
                        'size' => 12
                    ])
                );
            $this->word_style->put('image', collect(['width_max' => 480]));

            // 實體化
            $this->php_word = new PhpWord();
            // 加載預設值
            $this->php_word->setDefaultFontName($this->word_style['font_default']['name']);
            $this->php_word->setDefaultFontSize($this->word_style['font_default']['size']);
            $this->php_word->addLinkStyle('LinkStyle', array('color' => '808000'));
            $this->section = $this->php_word->addSection(
                array(
                    'marginLeft' => 1000, 'marginRight' => 1000,
                    'marginTop' => 1000, 'marginBottom' => 1000
                )
            );

            $this->word_style['title']->map(function ($item, $key) {
                $this->php_word->addTitleStyle(
                    $item['depth'],
                    (!empty($item['fontStyle'])) ? $item['fontStyle'] : [],
                    (!empty($item['paragraphStyle'])) ? $item['paragraphStyle'] : []
                );
            });
        } else {
            $temp_travels = TempTravel::select(
                'id',
                'travel_no'
            )->find($travel_id);
            $this->temp_travels = $temp_travels;
        }
        $service->setExecuteEnd('init');
        $this->execution_time[] = $service->getExecutionTime();
    }

    // -------------------- DOCX 設定 ------------------------------------------
    // ********** title設定 **********
    public function removeTitleStyle(int $depth): void
    {
        $this->word_style['title']
            ->map(function ($item, $key) use ($depth) {
                ($item['depth'] == $depth) && $this->word_style['title']->forget($key);
            });
    }
    // 儲存設定
    public function saveTitleStyle(): void
    {
        $this->word_style['title']->map(function ($item, $key) {
            $this->php_word->addTitleStyle(
                $item['depth'],
                (!empty($item['fontStyle'])) && $item['fontStyle'],
                (!empty($item['paragraphStyle'])) && $item['paragraphStyle']
            );
        });
    }
    // 增加一個到title style
    public function addTitleStyle(int $depth, array $fontStyle, array $paragraphStyle = []): void
    {
        $this->word_style['title']->push([
            'depth' => $depth,
            'fontStyle' => $fontStyle,
            'paragraphStyle' => $paragraphStyle
        ]);
    }

    // 取得title style 設定檔
    public function getTitleStyle(): object
    {
        return $this->word_style['title'];
    }

    // 取得 word style 全部設定
    public function getWordStyle(): object
    {
        return $this->word_style;
    }

    // 儲存 word style 
    public function saveWordStyle(object $word_style): void
    {
        $this->word_style = $word_style;
    }

    public function getApiInfo(): object
    {
        return $this->api_info;
    }

    // -------------------------------------------------------------------------
    // 建立檔案
    public function createDocx($show_info)
    {
        $service = new OfficeService();
        $service->setExecuteStart('create');
        if (!empty($this->err_log->toArray())) return $this->err_log->toArray();
        if ($this->api_data['info']['data'][0]['status'] == 1) return "no_data";
        // 換行
        $wbr = function () {
            $this->section->addTextBreak();
        };
        $wpb = function () {
            $this->section->addPageBreak();
        };
        // 出團名稱
        if ($show_info['travel_name']) {
            $this->section->addTitle(
                (!empty($this->api_data['s_total'])) ? $this->api_data['s_total'] : "喜　鴻　假　期",
                1
            );
            $wbr();
        }
        if ($show_info['travel_slogan']) {
            $temp_slogan = $this->section->addTextRun();
            if (!empty($this->api_data['info']['data'][0]['slogan'])) {
                $slogan =  $this->brFormat($this->api_data['info']['data'][0]['slogan']);
                $temp_slogan->addText('【' . $slogan[0] . '】', ['color' => 'ff0000', 'size' => '24']);
            }
            $title2 = $this->brFormat($this->api_data['info']['data'][0]['title_2']);
            $title2->map(function ($item) use (&$temp_slogan) {
                $temp_slogan->addText($item, ['size' => 24], ['align' => 'center']);
            });
            $wbr();
        }
        // 主題附註
        if ($show_info['travel_slogan_note']) {
            if (!empty($this->api_data['info']['data'][0]['title_3'])) {
                $str = $this->brFormat($this->api_data['info']['data'][0]['title_3']);
                $str->map(function ($item, $key) {
                    $this->section->addText($item);
                });
            }
        }
        // 優惠
        if ($show_info['discount']) {
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
            $str->map(function ($item) {
                $item = $this->brFormat($item);
                $item->map(function ($item2) {
                    $this->section->addText($item2);
                });
            });
        }
        // memo 警語
        if (!empty($this->api_data['info']['data'][0]['memo'])) {
            $temp_style = [];
            if (strpos($this->api_data['info']['data'][0]['memo'], "red") !== false) {
                $temp_style['color'] = "#ff0000";
            }
            $temp_str = strip_tags($this->api_data['info']['data'][0]['memo']);
            $temp_str = $this->brFormat($temp_str);
            $temp_str->map(function ($item) use ($temp_style) {
                $this->section->addText($item, $temp_style);
            });
        }
        // ********** icon    **********
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
            $icon_table = $this->section->addTable();
            $icon_table->addRow();
            $icon_content = $icon_table->addCell(10000)->addTextRun(['align' => 'right']);

            $promotions->map(function ($code) use ($icons, &$icon_content) {
                if (is_numeric($code)) return;
                $img_url = storage_path() . '/travel/imgs/promotions/' . $icons[$code]['url'];
                $icon_content->addImage($img_url, ['width' => 50, 'align' => 'right']);
            });
        }
        // ********** 出發日期 **********
        $this->section->addText('出發日期：' . $this->api_data['info']['data'][0]['date'], ['size' => '12', 'color' => '006666'], ['align' => 'right']);
        $wbr();

        // ********** 行程特色 **********

        if ($show_info['schedule_feature']) {
            $this->section->addTitle('行程特色', 4);
            // $this->section->addText(
            //     '行程特色',
            //     ['bold' => true, 'color' => 'ff0000', 'size' => 18],
            //     ['align' => 'center']
            // );
            $wbr();
            $this->scheduleFeature($show_info['schedule_feature_image']);
            // 費用說明
            if (!empty($this->api_data['info']['data'][0]['price_memo'])) {
                $this->section->addText(
                    '費用說明',
                    ['color' => "ff0000", 'size' => 18]
                );

                $str = $this->brFormat($this->api_data['info']['data'][0]['price_memo']);
                // $str = $this->remove_emoji($str);
                $str->map(function ($item, $key) {

                    $search = "FONTCOLOR=";
                    if (preg_match("/{$search}/i", $item)) {
                        $item = strip_tags($item);
                        $this->section->addText($item, ['color' => 'ff0000']);
                    } else {
                        $this->section->addText($item);
                    }
                });
            }
            $wpb();
            // $this->section->addPageBreak();
        }


        // \PhpOffice\PhpWord\Shared\Html::addHtml($this->section,$str);
        // ********** 行程內容 **************************************************

        $this->scheduleDay(
            $show_info['schedule_content'],
            $show_info['schedule_special_note'],
            $show_info['schedule_kind_reminder'],
            $show_info['schedule_serve'],
            $show_info['schedule_attraction'],
            $show_info['schedule_attraction_introduce'],
            $show_info['schedule_attraction_image']
        );
        $wpb();
        // $this->section->addPageBreak();

        // ********** 注意事項 please_note***************************************
        if ($show_info['please_note']) {
            $this->pleaseNote();
            $wbr();
            // $this->section->addPageBreak();
        }

        // ********************* 航班資訊 ***************************************
        $this->flightInfo();
        $wbr();
        // $this->section->addPageBreak();

        // ********* 旅遊注意事項 ************************************************
        // travel_please_note
        if ($show_info['travel_please_note']) {
            $this->travelNote();

            // $this->section->addPageBreak();
        }
        // ********************* 團費參考 ***************************************
        if ($show_info['price']) {
            $this->section->addTextBreak();
            $this->price();
        }
        // ********************* 業務員 *****************************************
        if ($show_info['sales_data']) {
            $this->sales();
            $this->section->addTextBreak();
            // $this->section->addPageBreak();
        }



        // ********************* 頁腳 *******************************************
        // $footer = $this->section->addFooter();
        // $footer->addPreserveText('第 {PAGE} 頁，共 {NUMPAGES} 頁', ['size' => 10], array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($this->php_word, 'Word2007');

        try {
            $writer->save(storage_path($this->file_info['path'] . $this->file_info['name'] . '.docx'));
        } catch (\Exception $e) {
        }
        // TODO: 轉檔
        // 修改完成需移除註解
        $this->image_paths->map(function ($item) {
            if (!empty($item)) {
                unlink($item);
            }
        });
        $service->setExecuteEnd('create');
        $this->execution_time[] = $service->getExecutionTime();
        return [storage_path($this->file_info['path'] . $this->file_info['name'] . '.docx'), $this->file_info['name']];
    }

    public function getExecuteTime()
    {
        return $this->execution_time;
    }

    public function imageFormate(string $image_info): object
    {
        $images = collect(array_filter(explode(',', $image_info)));
        $search = ['width', 'height'];
        $images->map(function ($item, $key) use ($search, $images) {
            if (preg_match("/{$search[0]}/i", $item) | preg_match("/{$search[1]}/i", $item) | (empty($item))) {
                $images->forget($key);
            }
        });
        return $images;
    }

    public function brFormat($str): object
    {
        $replace = '<br />';
        $search = collect([
            '<br/>', '<br>', '</br>', '<BR>', '</BR>', '<BR/>', '</ BR>',
            '<BR />', '<Br>', '</Br>', '<Br/>', '</ Br>', '<Br />', '<br />'
        ]);
        // // 濾除&#XXXX;
        // $pattern = '/\&\#[0-9]{4};/';

        $search->map(function ($item) use (&$str, $replace) {
            $str = str_replace($item, $replace, $str);
        });

        $str = collect(explode('<br />', $str));
        $str = $str->map(function ($item) {
            $temp_str = $item;
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
        return $str;
    }


    // 行程特色
    public function scheduleDay(
        $schedule_content,
        $schedule_special_note,
        $schedule_kind_reminder,
        $schedule_serve,
        $schedule_attraction,
        $schedule_attraction_introduce,
        $schedule_attraction_image
    ) {

        $flight_data = collect($this->api_data['flight']['data']);
        $flight_data = $flight_data->map(function ($item) {
            $temp_arr = [];
            // if (strstr($item['time_2'], '+')) {
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

        $first_day = (!empty($flight_data[0]) ? $flight_data[0] : 0);
        $total_day = count($this->api_data['schedule']['data']);
        $end_day = ($first_day != 0) ? $flight_data[count($flight_data) - 1] : 0;
        $show_day = ($end_day != 0) ? $total_day - $end_day['num'] : 0;

        // schedule_content 日行程內容
        $styleTable = array('cellMargin' => 100, 'borderSize' => 6, 'borderColor' => 'd7d7d7', 'align' => 'center');
        $this->section->addTitle('行程內容', 4);

        $this->section->addTextBreak();
        $table = $this->section->addTable($styleTable);
        $cellRowSpan = array('vMerge' => 'restart', 'borderSize' => 6, 'alignment' => Jc::CENTER);
        $cellRowContinue = array('vMerge' => 'continue', 'borderSize' => 6);


        foreach ($this->api_data['schedule']['data'] as $key => $day) {

            $table->addRow();
            $table->addCell(500, $cellRowSpan)->addText('第' . $day['day'] . '天', ['bold' => true], ['alignment' => Jc::CENTER]);
            // 每日行程摘要
            $content = $table->addCell(2000, ['borderSize' => 6, 'gridSpan' => 2, 'vMerge' => 'restart']);
            // dd($day['abstract_1']);
            $abstract_1 = $this->brFormat($day['abstract_1']);
            $content->addText('【' . $abstract_1[0] . '】', ['bold' => true]);
            if ($day['day'] == 1) {
                if (!empty($first_day['text'])) {
                    $content->addText($first_day['text']);
                }
            }
            if ($day['day'] == $show_day) {
                $content->addText($end_day['text']);
            }
            if ($schedule_content) {
                if (!empty($day['memo_1'])) {
                    $memo_1 = $this->brFormat($day['memo_1']);
                    $memo_1->map(function ($item) use (&$content) {
                        $content->addText($item);
                    });
                }
            }

            // 如果view有資料則使用view，如果沒有則使用memo_1
            if ($schedule_attraction) {
                if (!empty($day['view'])) {

                    // 圖片 + 簡介摘要
                    if ($schedule_attraction_introduce && $schedule_attraction_image) {
                        foreach ($day['view'] as $kkk => $view) {

                            $images = trim($view['images'], 'https:');
                            $table->addRow();
                            $table->addCell(500, $cellRowContinue);
                            if (!empty($images)) {
                                $table->addCell(3500, ['borderSize' => 6])
                                    ->addImage($images, ['width' => 150, 'align' => 'center']);
                            } else {
                                $table->addCell(3500, ['borderSize' => 6])->addText('');
                            }

                            $content = $table->addCell(6000, ['borderSize' => 6]);
                            $view_name = $this->brFormat($view['name']);
                            $content->addText('【' . $view_name[0] . '】', ['bold' => true]);
                            $content->addTextBreak();
                            $feature = $this->brFormat($view['memo_2']);

                            $feature->map(function ($paragraph) use (&$content) {
                                $content->addText($paragraph); // 特色簡介
                                $content->addTextBreak();
                            });

                            if (!empty($view['memo_3'])) {
                                // ※
                                // $content->addText('※', ['color' => 'ff0000']);
                                $warning = $this->brFormat($view['memo_3']); // 特別備註 紅字
                                $warning->map(function ($paragraph) use (&$content) {
                                    $content->addText($paragraph, ['color' => 'ff0000']); // 特色簡介
                                });
                            }

                            $content->addTextBreak();
                        }
                    }
                    // 圖片
                    if ($schedule_attraction_image == true && $schedule_attraction_introduce == false) {
                        $table->addRow();
                        $table->addCell(500, $cellRowContinue);
                        $content = $table->addCell(9500, ['borderRightSize' => 6, 'gridSpan' => 2])->addTextRun();

                        foreach ($day['view'] as $kkk => $view) {
                            if ($view['images'] != '') {
                                $images = trim($view['images'], 'https:');
                                $content->addImage($images, ['width' => 150, 'align' => 'center']);
                            } else {
                                $content->addText('');
                            }
                        }
                    }

                    if ($schedule_attraction_introduce == true && $schedule_attraction_image == false) {
                        foreach ($day['view'] as $kkk => $view) {
                            $table->addRow();
                            $table->addCell(500, $cellRowContinue);
                            $content = $table->addCell(9500, ['borderSize' => 6, 'gridSpan' => 2]);
                            $content->addText('【' . $view['name'] . '】', ['bold' => true]);
                            $content->addTextBreak();
                            $feature = $this->brFormat($view['memo_2']);

                            $feature->map(function ($paragraph) use (&$content) {
                                $content->addText($paragraph); // 特色簡介
                                $content->addTextBreak();
                            });

                            if (!empty($view['memo_3'])) {
                                // ※
                                $content->addText('※', ['color' => 'ff0000']);
                                $warning = $this->brFormat($view['memo_3']); // 特別備註 紅字
                                $warning->map(function ($paragraph) use (&$content) {
                                    $content->addText($paragraph, ['color' => 'ff0000']); // 特色簡介
                                });
                            }

                            $content->addTextBreak();
                        }
                    }
                }
            }
            // 用餐 serve 
            if ($schedule_serve) {
                $table->addRow();
                $table->addCell(500, $cellRowContinue);
                $serve = $table->addCell(2000, ['borderSize' => 6, 'gridSpan' => 2, 'vMerge' => 'restart']);
                $breakfast = $this->brFormat($day['breakfast']);
                $lunch = $this->brFormat($day['lunch']);
                $dinner = $this->brFormat($day['dinner']);
                $serve->addText('早餐：' . $breakfast[0] . '　中餐：' . $lunch[0] . '　晚餐：' . $dinner[0]);
            }

            // 住宿 accommodation
            $table->addRow();
            $table->addCell(500, $cellRowContinue);
            $accommodation = $table->addCell(2000, ['borderSize' => 6, 'gridSpan' => 2, 'vMerge' => 'restart']);
            $textRun = $accommodation->addTextRun();
            $textRun->addText('住宿資訊： ');
            $temp_name = '';
            if (!empty($day['hotel']['data'])) {
                foreach ($day['hotel']['data'] as $hotel) {

                    $temp_name = (!empty($hotel['name'])) ? $hotel['name'] : "";
                    $hotel_url = (!empty($hotel['url'])) ? $hotel['url'] : "";
                    $hotel_name = (!empty($hotel['name'])) ? $hotel['name'] : "";

                    if (!empty($hotel['url'])) {
                        $hotel_name = $this->brFormat($hotel_name);

                        $textRun->addLink(
                            $hotel_url,
                            $hotel_name[0],
                            array(
                                'color' => '0000FF',
                                'underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE
                            )
                        );
                    } else {
                        $hotel_name = $this->brFormat($hotel_name);
                        $hotel_name->map(function ($item) use ($textRun) {
                            $textRun->addText($item);
                        });
                    }
                    if ($temp_name !== '溫暖的家' && $temp_name !== '夜宿機上') {
                        $textRun->addText(' 或 ');
                    }
                }
            }
            if ($temp_name !== '溫暖的家' && $temp_name !== '夜宿機上') {
                $textRun->addText('同級');
            }

            // 溫馨提醒
            if ($schedule_kind_reminder) {
                if (!empty($day['memo_3'])) {
                    $table->addRow();
                    $table->addCell(500, ['vMerge' => 'continue', 'borderSize' => 6]);
                    $special_note = $table->addCell(2000, ['borderSize' => 6, 'gridSpan' => 2, 'vMerge' => 'restart', 'bgColor' => "f9fff0"]);
                    $special_note->addText('溫馨提醒', ['bold' => true, 'color' => '339900']);
                    $special_notes = $this->brFormat($day['memo_3']);
                    $special_notes->map(function ($item2, $key) use (&$special_note) {
                        $special_note->addText($item2);
                    });
                    $special_note->addTextBreak();
                }
            }
            // 特別說明
            if ($schedule_special_note) {
                if (!empty($day['memo_2'])) {
                    $table->addRow();
                    $table->addCell(500, ['vMerge' => 'continue', 'borderSize' => 6]);
                    $special_note = $table->addCell(2000, ['borderSize' => 6, 'gridSpan' => 2, 'vMerge' => 'restart', 'bgColor' => "fff4f4"]);
                    $special_note->addText('特別說明', ['bold' => true, 'color' => 'ff6666']);
                    // $special_note->addTextBreak();
                    $special_notes = $this->brFormat($day['memo_2']);
                    $special_notes->map(function ($item2, $key) use (&$special_note) {
                        $special_note->addText($item2);
                    });
                    $special_note->addTextBreak();
                }
            }
        }
    }

    // 行程特色
    public function scheduleFeature($show_image = false)
    {

        $replace = function ($str, $arr) {
            $temp_str = $str;
            foreach ($arr as $key => $val) {
                $temp_str = str_replace($val['search'], $val['replace'], $temp_str);
            }
            return $temp_str;
        };
        // dd($this->api_data['feature']['data']);
        foreach ($this->api_data['feature']['data'] as $key => $item) {
            if ((!empty($item['name'])) && (!empty($item['content']))) {
                // if($item['name']=="費用說明") dd($item);
                $this->section->addTitle($item['name'], 5); // 特色標題
                $this->section->addTextBreak();
                $content = $this->brFormat($item['content']); // 特色內容
                $content->map(function ($value, $key) use ($replace) {
                    $str = $replace($value, [
                        ['search' => '<BR>', 'replace' => '<br />'],
                        ['search' => '</FONT>', 'replace' => ''],
                        ['search' => '<FONT>', 'replace' => ''],
                        ['search' => '<br>', 'replace' => '<br />'],
                    ]);

                    $search = ["<FONT", '<Font', '<font'];

                    if (preg_match("/{$search[0]}/i", $str) | preg_match("/{$search[1]}/i", $str) | preg_match("/{$search[2]}/i", $str)) {
                        $str = strip_tags($str);
                        $str = filter_var($str, FILTER_DEFAULT);
                        $str = $this->brFormat($str);
                        $str->map(function ($item) {
                            $this->section->addText($item, ['color' => 'ff0000']);
                        });
                    } else {
                        $search_a = "href=";

                        if (preg_match("/{$search_a}/i", $str)) {
                            $str = $replace($str, [
                                ['search' => '//', 'replace' => ''],
                                ['search' => '<a ', 'replace' => ' '],
                                ['search' => 'href=', 'replace' => ' 網址：'],
                                ['search' => '</a>', 'replace' => '  '],
                                ['search' => '>', 'replace' => ' '],
                                ['search' => 'target="_blank"', 'replace' => ''],
                            ]);
                        }
                        $str = $this->brFormat($str);
                        $str->map(function ($item) {
                            $this->section->addText($item);
                        });
                    }
                });
            }

            if ($show_image && !empty($item['images'])) {
                $images = $this->imageFormate($item['images']);
                $images->map(function ($value, $key) {
                    $size = getimagesize($value);
                    $width = 0;
                    $height = 0;
                    $width = ($size[0] > 480) ? 480 : $size[0];

                    $this->section->addImage($value, [
                        'width'         => $width,
                        'align' => 'center',
                    ]);
                });
            }
            $this->section->addTextBreak();
        }
        $this->section->addTextBreak();
    }

    // 注意事項
    public function pleaseNote()
    {
        $style = ['cellMargin' => 100, 'borderSize' => 6, 'borderColor' => 'd7d7d7', 'align' => 'center'];
        $table = $this->section->addTable($style);
        $table->addRow();
        $table->addCell(10000, ['borderSize' => 6])
            ->addText('注意事項', ['size' => 18, 'color' => 'ff0000', 'bold' => true], ['align' => 'center']);
        foreach ($this->api_data['detail_note'] as $key => $value) {
            if (!empty($value['title'])) {
                $table->addRow();
                $content = $table->addCell(10000, ['borderSize' => 6]);
                $title = $this->brFormat($value['title']);
                $title->map(function ($item) use (&$content) {
                    $content->addText($item, ['size' => 18, 'bold' => true]);
                });

                $content->addTextBreak();
                foreach ($value['data'] as $value2) {
                    if (!empty($value2['text'])) {
                        $str = $this->brFormat($value2['text']);
                        $str->map(function ($item) use (&$content) {
                            if (!empty($item)) {
                                $content->addText($item);
                                $content->addTextBreak();
                            }
                        });
                    }
                }
            }
        }
        // $styleTable = array('cellMargin' => 100, 'borderSize' => 6, 'borderColor' => 'd7d7d7', 'align' => 'center');
        // $note_table = $this->section->addTable($styleTable);
        // $note_table->addRow();
        // $note_table->addCell(10000, ['borderSize' => 6])
        //     ->addText('注意事項', ['size' => 18, 'color' => 'ff0000', 'bold' => true], ['align' => 'center']);

        // foreach ($this->api_data['detail_note'] as $key => $value) {
        //     if (!empty($value['title'])) {
        //         $note_table->addRow();
        //         $content = $note_table->addCell(10000, ['borderSize' => 6]);
        //         $title = $this->brFormat($value['title']);
        //         $title->map(function ($item) use (&$content) {
        //             $content->addText($item, ['size' => 18, 'bold' => true]);
        //         });

        //         $content->addTextBreak();
        //         foreach ($value['data'] as $key2 => $value2) {
        //             if (!empty($value2['text'])) {
        //                 $str = $this->brFormat($value2['text']);
        //                 $str->map(function ($item) use (&$content) {
        //                     if (!empty($item)) {
        //                         $content->addText($item);
        //                         $content->addTextBreak();
        //                     }
        //                 });
        //             }
        //         }
        //     }
        // }
    }

    // 航班資訊
    public function flightInfo()
    {
        // $this->section->addPageBreak();
        $styleTable = array('cellMargin' => 100, 'borderSize' => 6, 'borderColor' => 'd7d7d7', 'align' => 'center');
        $flight_table = $this->section->addTable($styleTable);
        $flight_table->addRow();
        $flight_table->addCell(2000, ['borderSize' => 6, 'gridSpan' => 5])
            ->addText('航班資訊', ['size' => 18, 'color' => 'ff0000', 'bold' => true], ['align' => 'center']);

        // 欄位名稱設定
        $temp_add_cell = function ($temp_arr) use (&$flight_table) {
            foreach ($temp_arr as $key => $value) {
                $bg_color = (!empty($value['bgColor'])) ? $value['bgColor'] : "ffffff";
                if (!empty($value['text'])) {
                    $flight_table->addCell($value['width'], ['borderSize' => 6, 'bgColor' => $bg_color])->addText($value['text'], [], ['align' => 'center']);
                } else {
                    $flight_table->addCell($value['width'], ['borderSize' => 6, 'bgColor' => $bg_color]);
                }
            }
        };
        $flight_table->addRow();
        $temp_add_cell([
            [
                'text' => '',
                'width' => 1500,
                'bgColor' => "cccccc"
            ],
            [
                'text' => '航班號碼',
                'width' => 1500,
                'bgColor' => "cccccc"
            ],
            [
                'text' => '搭乘日期',
                'width' => 2000,
                'bgColor' => "cccccc"
            ],
            [
                'text' => '起訖城市',
                'width' => 3000,
                'bgColor' => "cccccc"
            ],
            [
                'text' => '飛行時間',
                'width' => 2000,
                'bgColor' => "cccccc"
            ]
        ]);

        $type = [
            '1' => '去程航班',
            '2' => '中段航班',
            '3' => '回程航班'
        ];

        if (!empty($this->api_data['flight']['data'])) {
            foreach ($this->api_data['flight']['data'] as $key => $info) {
                $flight_table->addRow();
                $type_key = $info['type'];
                $title_str = $type[$info['type']];
                $temp_add_cell([
                    ['text' => $title_str, 'width' => 1500],
                    ['text' => $info['flight'], 'width' => 1500],
                    ['text' => $info['date'], 'width' => 2000],
                    ['text' => $info['place_1'] . '/' . $info['place_2'], 'width' => 3000],
                    ['text' => $info['time_1'] . '~' . $info['time_2'], 'width' => 2000]
                ]);
            }
        }
    }

    public function price()
    {
        // dd($this->api_data['info']['data'][0]);
        $data = $this->api_data['info']['data'][0]['price'];
        // $this->section->addPageBreak();
        $styleTable = array('cellMargin' => 100, 'borderSize' => 6, 'borderColor' => 'd7d7d7', 'align' => 'center');
        $price_table = $this->section->addTable($styleTable);
        $price_table->addRow();
        $price_table->addCell(1650, ['borderSize' => 6, 'gridSpan' => 6])
            ->addText('團費參考', ['size' => 18, 'color' => 'ff0000', 'bold' => true], ['align' => 'center']);
        $data_count = (count($data) % 3);
        // dd($data_count);
        foreach ($data as $key => $value) {
            ($key % 3 == 0) && $price_table->addRow();
            $price_table->addCell(1650, ['borderSize' => 6, 'bgColor' => 'cccccc'])->addText($value['name']);
            $price_table->addCell(1650, ['borderSize' => 6])->addText($value['value']);
        }
        ($data_count == 0) && $price_table->addRow();
        for ($i = 0; $i < (3 - $data_count); $i++) {
            $price_table->addCell(1650, ['borderSize' => 6, 'bgColor' => 'cccccc'])->addText('');
            $price_table->addCell(1650, ['borderSize' => 6])->addText('');
        }
    }

    public function travelNote()
    {
        if (!empty($this->api_data['travel_note'])) {
            $styleTable = array('cellMargin' => 100, 'borderSize' => 6, 'borderColor' => 'd7d7d7', 'align' => 'center');
            $note_table = $this->section->addTable($styleTable);
            $note_table->addRow();
            $note_table->addCell(10000, ['borderSize' => 6])
                ->addText($this->api_data['travel_note']['country'] . '旅遊注意事項', ['size' => 18, 'color' => 'ff0000', 'bold' => true], ['align' => 'center']);

            foreach ($this->api_data['travel_note']['data'] as $key => $value) {
                $note_table->addRow();
                $content = $note_table->addCell(10000, ['borderSize' => 6]);
                $title = $this->brFormat($value['title']);
                $title->map(function ($item) use (&$content) {
                    $content->addText($item, ['size' => 12, 'bold' => true]);
                });
                $content->addTextBreak();
                $str = $this->brFormat($value['content']);
                $str->map(function ($item, $key) use (&$content) {
                    $content->addText($item);
                });
            }
            $this->section->addPageBreak();
        }
    }

    public function sales()
    {
        if (empty($this->api_data['sales_data']['data'][0])) return;
        $styleTable = array('cellMargin' => 100, 'align' => 'center');
        // $this->section->addPageBreak();
        $sales_table = $this->section->addTable($styleTable);
        $sales_table->addRow();
        $sales_table->addCell(10000, ['gridSpan' => 2])
            ->addText('業務員資料', ['size' => 18, 'color' => 'ff0000', 'bold' => true], ['align' => 'center']);
        $sales_table->addRow();
        $sales_table->addCell(5000, [])->addText((!empty($this->api_data['sales_data']['data'][0]['company_name'])) ? $this->api_data['sales_data']['data'][0]['company_name'] : '***有限公司');
        $sales_table->addCell(5000, [])
            ->addText(
                '業務請洽 : '
                    . $this->api_data['sales_data']['data'][0]['name']
                    . " (" . $this->api_data['sales_data']['data'][0]['mobile'] . ")"
            );

        $sales_table->addRow();
        $sales_table->addCell(5000, [])->addText($this->api_data['sales_data']['data'][0]['company_address']);
        $row2 = $sales_table->addCell(5000, []);
        if (empty($this->api_data['sales_data']['data'][0]['company_name'])) {
            $row2->addText(
                'TEL : ' . $this->api_data['sales_data']['data'][0]['tel']
                    . ' 分機 '
            );
        }
        $row2->addText('FAX : ' . $this->api_data['sales_data']['data'][0]['fax']);
        $sales_table->addRow();
        if (!empty($this->api_data['sales_data']['data'][0]['company_name'])) {

            $sales_table->addCell(5000, [])->addText(
                'TEL : ' . $this->api_data['sales_data']['data'][0]['tel']
                    . ' 分機 '
            );
        } else {
            $sales_table->addCell(5000, [])->addText('http://www.*.com.tw/');
        }
        $sales_table->addCell(5000, [])
            ->addText('email : ' . $this->api_data['sales_data']['data'][0]['email']);
    }

    public function downloadPath()
    {
        return [
            'file_path' => storage_path('travel/temp/') . $this->temp_travels['travel_no'] . '.docx',
            'file_name' => $this->temp_travels['travel_no'] . '.docx'
        ];
    }

    // private function strReplace(array $search,string $replace,string $str){
    //     return str_replace($search,$replace,$str);
    // }
}
