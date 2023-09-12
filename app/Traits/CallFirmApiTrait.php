<?php

namespace App\Traits;

/** CallFirmApiTrait
 *  call 商工行 api
 */
trait CallFirmApiTrait
{
    /** getFrimInfo()
     *  call 商工行 api 取得公司基本資料
     *  @param string $gui_number 統一編號
     *  @param integer $type 2.公司登記基本資料-應用一|4.公司登記基本資料-應用三|8.商業登記基本資料-應用一|28.統編查分公司資料|47.商業登記基本資料
     *  @return array company base information
     */
    public function getFrimInfo($gui_number, $type)
    {
        /** 2.公司登記基本資料-應用一
         *https://data.gcis.nat.gov.tw/od/data/api/F05D1060-7D57-4763-BDCE-0DAF5975AFE0?
         *$format=xml&
         *$filter=Business_Accounting_NO eq 20828393&
         *$skip=0&$top=50
        
         *必填 $format 帶入參數 1.json 2.xml
         *必填 $filter=Business_Accounting_NO eq {Business_Accounting_NO} Business_Accounting_NO:統一編號
         *選填 $skip={skip} 從第n筆開始條列	：為可選條件：	請填入阿拉伯數字，預設為0，下限為0，上限為500000
         *選填 $top={top} 每次可撈取筆數	：為可選條件：	請填入阿拉伯數字，預設為50，下限為1，上限為1000
         */
        switch ($type) {
            case '2':
                $uri = 'https://data.gcis.nat.gov.tw/od/data/api/*?';
                // $params = array(
                //     '$format' => 'json',
                //     '$filter' => 'Business_Accounting_NO eq ' . $gui_number,
                //     '$skip' => '0',
                //     '$top' => '50'

                // );
                $params = $this->getArray($gui_number,'B');
                break;
            case '3':
                $uri = 'https://data.gcis.nat.gov.tw/od/data/api/*?';
                // $params = array(
                //     '$format' => 'json',
                //     '$filter' => 'Business_Accounting_NO eq ' . $gui_number,
                //     '$skip' => '0',
                //     '$top' => '50'

                // );
                $params = $this->getArray($gui_number,'B');
                break;
            case '4':
                $uri = 'https://data.gcis.nat.gov.tw/od/data/api/*C?';
                // $params = array(
                //     '$format' => 'json',
                //     '$filter' => 'Business_Accounting_NO eq ' . $gui_number,
                //     '$skip' => '0',
                //     '$top' => '50'

                // );
                $params = $this->getArray($gui_number,'B');
                break;
            case '8':
                $uri = 'https://data.gcis.nat.gov.tw/od/data/api/*?';
                $params = $this->getArray($gui_number,'P');
                // $params = array(
                //     '$format' => 'json',
                //     '$filter' => 'President_No eq ' . $gui_number,
                //     '$skip' => '0',
                //     '$top' => '50'

                // );
                break;
            case '28':
                $uri = 'https://data.gcis.nat.gov.tw/od/data/api/*?';
                $params = $this->getArray($gui_number,'B');
                // $params = array(
                //     '$format' => 'json',
                //     '$filter' => 'Business_Accounting_NO eq ' . $gui_number,
                //     '$skip' => '0',
                //     '$top' => '50'

                // );
                break;
            case '47':
                $uri = 'https://data.gcis.nat.gov.tw/od/data/api/*?';
                $params = $this->getArray($gui_number,'P');
                // $params = array(
                //     '$format' => 'json',
                //     '$filter' => 'President_No eq ' . $gui_number,
                //     '$skip' => '0',
                //     '$top' => '50'
                // );
                break;
            case '48':
                $uri = 'https://data.gcis.nat.gov.tw/od/data/api/*?';
                $params = $this->getArray($gui_number,'B');
                // $params = array(
                //     '$format' => 'json',
                //     '$filter' => 'Business_Accounting_NO eq ' . $gui_number,
                //     '$skip' => '0',
                //     '$top' => '50'

                // );
                break;
        }

        // $params = array(
        //     '$format' => 'json',
        //     '$filter' => 'Business_Accounting_NO eq ' . $gui_number,
        //     '$skip' => '0',
        //     '$top' => '50'

        // );
        $url = $uri . http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file_contents = curl_exec($ch);
        curl_close($ch);
        return json_decode($file_contents, true);
    }

    public function getArray($gui_number, $type)
    {
        $params = [];
        switch ($type) {
            case 'B':
                $params = array(
                    '$format' => 'json',
                    '$filter' => 'Business_Accounting_NO eq ' . $gui_number,
                    '$skip' => '0',
                    '$top' => '50'

                );
                break;
            case 'P':
                $params = array(
                    '$format' => 'json',
                    '$filter' => 'President_No eq ' . $gui_number,
                    '$skip' => '0',
                    '$top' => '50'

                );
                break;
        }

        return $params;
    }
}
