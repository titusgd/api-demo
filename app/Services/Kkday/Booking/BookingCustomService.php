<?php

namespace App\Services\Kkday\Booking;

use Illuminate\Http\Request;
use App\Services\Kkday\KkdayService;

/**
 * Class BookingCustomService.
 */
class BookingCustomService extends KkdayService
{
    /**
     * @param $req 發送給 kkday
     * @param $data kkday 回傳的資料
     * @return mixed
     */
    public function parse($req, $data)
    {
        if (empty($data['custom']) || empty($req['custom'])) {
            return response()->json(['01', '', 'data or req is empty']);
        }

        // 檢查 cus_type 是否有對應
        if (!$this->isCusTypeCountMatching($req['custom'], $data['custom'])) {
            return response()->json(['01', '', 'cus_type is not match']);
        }

        // 檢查 custom 是否為必填
        $requiredFields = $this->getRequiredFields($data['custom']);

        if ($requiredFields) {
            $reqField = $this->hasEmptyRequiredFields($req['custom'], $requiredFields);
            if ($reqField) {
                return response()->json(['code' => '01', 'msg' => '', 'data' => $reqField]);
            }
        }

        // 檢查 is_perParticipant 是否為 true
        $isPerParticipant = $this->getPerParticipant($req, $data['custom']);
        if ($isPerParticipant['check'] == true) {
            if ($isPerParticipant['participant'] == false) {
                return response()->json(['code' => '01', 'msg' => '', 'data' => 'is_perParticipant']);
            }

            // 檢查 shoe_type, shoe_unit, 然後檢查 shoe 是否正確, is_perParticipant = true
            $shoe = $this->getParticipantShoe($req, $data['custom'], true);
            if ($shoe['check'] == true) {
                if ($shoe['shoe'] == false) {
                    return response()->json(['code' => '01', 'msg' => '', 'data' => 'shoe']);
                }
            }
        } else {
            // 檢查 shoe_type, shoe_unit, is_perParticipant = false
            $shoe = $this->getParticipantShoe($req, $data['custom']);
            if ($shoe['check'] == true) {
                if ($shoe['shoe'] == false) {
                    return response()->json(['code' => '01', 'msg' => '', 'data' => 'shoe']);
                }
            }
        }

        return response()->json(['code' => '00', 'msg' => 'OK', 'data' => $req]);
    }

    private function isCusTypeCountMatching($req, $data)
    {
        $dataCusTypeArray = array_filter($data['cus_type']['list_option']);
        $reqCusTypeCount = array_column($req, 'cus_type');
        $cusTypeDiff = array_diff($dataCusTypeArray, $reqCusTypeCount);

        return count($cusTypeDiff) === 0;
    }

    private function getRequiredFields($data)
    {
        $requiredFields = [];

        foreach ($data as $key => $value) {
            if ($value['is_require'] == 'True' && $key != 'cus_type') {
                $requiredFields[] = $key;
            }
        }

        return $requiredFields;
    }


    private function hasEmptyRequiredFields($req, $requiredFields)
    {

        $fields = [];
        foreach ($req as $value) {
            foreach ( $value as  $k => $v) {
                if($k != 'cus_type'){
                    if(in_array($k, $requiredFields)){
                        $fields[] = $k;
                    }
                }
            }
        }

        $fieldsArray = array_unique($fields);
        $fieldsDiff = array_diff($requiredFields, $fieldsArray);

        $response = [];
        if(count($fieldsDiff) > 0){
            foreach($fieldsDiff as $key => $value){
                $response[] = $value;
            }
            return $response;
        }
        return $response;
    }

    private function getPerParticipant($req, $data)
    {

        $isPerParticipant = false;
        $skusQty = $req['skus'][0]['qty'];

        foreach ($data as $key => $value) {
            if (!empty($value['is_perParticipant']) && $value['is_perParticipant'] == 'True') {
                $isPerParticipant = true;
                break;
            }
        }

        $res = [
            'check' => false,
            'participant' => false,
        ];

        if ($isPerParticipant == true) {
            $cus_type_count = 0;

            foreach ($req['custom'] as $key => $value) {
                if (!empty($value['cus_type'])) {
                    if ($value['cus_type'] == 'cus_01' || $value['cus_type'] == 'cus_02') {
                        $cus_type_count++;
                    }
                }
            }

            if ($skusQty == $cus_type_count) {
                $res = [
                    'check' => true,
                    'participant' => true
                ];
            } else {
                $res = [
                    'check' => true,
                    'participant' => false
                ];
            }
        }

        return $res;
    }


    // 檢查 shoe, 分為 is_perParticipant = true, false
    private function getParticipantShoe($req, $data, $participant = false)
    {
        $shoeType = [];
        $shoeUnit = [];
        $isPerParticipant = false;

        if ($participant) {
            foreach ($data as $value) {
                if (!empty($value['is_perParticipant']) && $value['is_perParticipant'] === 'True') {
                    $isPerParticipant = true;
                    break;
                }
            }
        }

        foreach ($req['custom'] as $value) {
            if (!empty($value['shoe_type'])) {
                $shoeType[] = $value['shoe_type'];
            }
            if (!empty($value['shoe_unit'])) {
                $shoeUnit[] = $value['shoe_unit'];
            }
        }

        $shoeTypeCount = count($shoeType);
        $shoeUnitCount = count($shoeUnit);
        $customCount = count($req['custom']);

        $shoeCheck = $shoeTypeCount === $customCount;
        $shoe = [
            'check' => $isPerParticipant || $shoeTypeCount === 0 || $shoeCheck,
            'shoe' => $shoeCheck
        ];

        return $shoe;
    }
}
