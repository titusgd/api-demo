<?php
namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use App\Traits\CustomResponseTrait;
/** ValidatorResponseTrait
 *  輸入驗證
 * 
*/
trait ValidatorResponseTrait{

    use CustomResponseTrait;
    /** checkValiDate
     *  只回傳第一筆錯誤訊息
    */
    public function checkValiDate($validate)
    {
        if ($validate->fails()) {
            list($code, $message) = explode(" ", $validate->errors()->first());
            return $this->response($code, $message);
        }
    }
    /** validatorAndResponse()
     *  資料驗證，並回傳一筆錯誤，response 格式。
     *  @param array $data 檢測資料
     *  @param array $relus 檢測條件
     *  @param array $message 錯誤訊息
     *  @param array $customAttributes
     *  @return obj
     */
    public function validatorAndResponse($data, $relus, $message = [], $customAttributes = [])
    {
        $vali = Validator::make($data, $relus, $message, $customAttributes);
        $errors = $this->checkValiDate($vali);
        if ($errors) {
            return $errors;
        }
    }
}