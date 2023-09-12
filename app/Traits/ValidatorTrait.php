<?php
namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use App\Traits\CustomResponseTrait;
/** ValidatorResponseTrait
 *  輸入驗證
 *
*/
trait ValidatorTrait{

    use CustomResponseTrait;

    public function validData($data, $rules, $msg) {
        $vali = Validator::make($data, $rules, $msg);

        $errors = $this->checkValiData($vali);
        if ($errors) {
            return $errors;
        } else {
            return '';
        }
    }

    public function checkValiData($validate)
    {
        if ($validate->fails()) {

            $chk = array_keys($validate->errors()->toArray());

            if ( strpos($chk[0],".") !==false ) {
                $msg = explode(".",$chk[0]);
                    return $this->response(
                        '01',
                        [
                            "index"=>$msg[0],
                            "key"=>$msg[1]
                        ]
                    );
            } else {
                list($code, $message) = explode(" ", $validate->errors()->first());
                return $this->response($code, $message);
            }
        }
    }
}
