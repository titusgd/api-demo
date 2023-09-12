<?php

namespace App\Traits;

trait DateTrait
{
    /** dateFormat
     *  日期格式化
     *  @param string $date Y/m/d、Y/m/d H:i:s
     *  @return array ['Y/m/d','']、['Y/m/d','H:i:s']
    */
    public function dateFormat($date)
    {
        // 輸入日期格式 
        // 1. Y/m/d
        // 2. Y/m/d H:i:s
        $date_exploded = explode(' ', $date);
        $date_count = count($date_exploded);
        switch ($date_count) {
            case 1:
                break;
                $date[] = str_replace('-','/',$date);
                $date[] = '';
            case 2:
                $date = new \DateTime($date);
                $date = explode(' ', $date->format('Y/m/d H:i:s'));
                break;
        }
        return $date;
    }

    /** dateROCToAD()
     *  民國轉西元年
     *  @param string $str 民國 yyymmdd 或 yyy/mm/dd 或yyy-mm-dd
     *  @param string $replace
     *  @return string yyyy-mm-dd
     */
    public function dateROCToAD($str, $replace)
    {
        $str = str_replace('/', '', $str);
        $str = str_replace('-', '', $str);
        $data = [];
        $date_array = str_split($str);
        $roc = [];
        array_push($roc, $date_array[0]);
        array_push($roc, $date_array[1]);
        array_push($roc, $date_array[2]);

        $mm = [];
        array_push($mm, $date_array[3]);
        array_push($mm, $date_array[4]);
        $dd = [];
        array_push($dd, $date_array[5]);
        array_push($dd, $date_array[6]);

        $roc = implode('', $roc);
        $mm = implode('', $mm);
        $dd = implode('', $dd);

        $ad = $roc + 1911;

        $date = implode($replace, [$ad, $mm, $dd]);
        return $date;
    }

    /** dateADToRoc()
     *  民國轉西元年
     *  @param string $str 西元 yyyymmdd 或 yyyy/mm/dd 或 yyyy-mm-dd
     *  @param string $replace
     *  @return string yyyy-mm-dd
     */
    public function dateADToRoc($str, $replace)
    {
        $str = str_replace('/', '', $str);
        $str = str_replace('-', '', $str);

        $data = [];
        $date_array = str_split($str);
        $ad = [];
        array_push($ad, $date_array[0]);
        array_push($ad, $date_array[1]);
        array_push($ad, $date_array[2]);
        array_push($ad, $date_array[3]);

        $mm = [];
        array_push($mm, $date_array[4]);
        array_push($mm, $date_array[5]);
        $dd = [];
        array_push($dd, $date_array[6]);
        array_push($dd, $date_array[7]);

        $ad = implode('', $ad);
        $mm = implode('', $mm);
        $dd = implode('', $dd);

        $roc = $ad - 1911;

        $date = implode($replace, [$roc, $mm, $dd]);
        return $date;
    }

    /** getDay()
     *  取得指定日期月份的天數，
     */
    public function getDay($date)
    {
        return date("t", strtotime($date));
    }

    /** toMinute()
     *  h:i 轉 分鐘數
     *  @param String $time h:i| h:i:s
     */
    public function toMinute($time)
    {
        $time = explode(":", $time);
        try {
            $time = (empty($time[2])) ? ($time[0] * 60) + $time[1] : (($time[0] * 60) + $time[1]) + (number_format($time[2] / 60, 2));
            return $time;
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    }
    /** dateToObject()
     *  日期時間轉換為陣列
     *  @param string $datetime 日期時間 Y-m-d H:i:s
     *  @return object 
    */
    public function dateToObject($datetime){
        $date_object = new \DateTime($datetime);
        return  $date_object;
    }
}
