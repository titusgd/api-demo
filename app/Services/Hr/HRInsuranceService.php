<?php

namespace App\Services\Hr;

use App\Services\Service;

use App\Models\Hr\HumanResource;
use App\Models\Hr\HumanResourceInsurance;


/**
 * Class HRInsuranceService.
 */
class HRInsuranceService extends Service
{
    public $validationRules = [
        'change.enrollment' => 'date',
        'change.withdrawal' => 'date',
        "group.enrollment" => 'date',
        "group.withdrawal" => 'date',
        "laborHealth.enrollment" => 'date',
        "laborHealth.withdrawal" => 'date',
        "labor.amount" => 'integer',
        "labor.deductible" => 'integer',
        "labor.pensio" => 'integer',
        "health.amount" => 'integer',
        "health.deductible" => 'integer',
        "health.family" => 'string',
        "appropriation.company" => 'integer',
        "appropriation.self" => 'integer',
    ];

    public $validationMsg = [
        'change.enrollment.date' => '01 change_enrollment',
        'change.withdrawal.date' => '01 change_withdrawal',
        'group.enrollment.date' => '01 group_enrollment',
        'group.withdrawal.date' => '01 group_withdrawal',
        'laborHealth.enrollment.date' => '01 labor_health_enrollment',
        'laborHealth.withdrawal.date' => '01 labor_health_withdrawal',
        'labor.amount.integer' => '01 labor_amount',
        'labor.pensio.integer' => '01 labor_pensio',
        'labor.deductible.integer' => '01 labor_deductible',
        'health.amount.integer' => '01 health_amount',
        'health.deductible.integer' => '01 health_deductible',
        'health.family.string' => '01 health_family',
        'appropriation.company.integer' => '01 appropriation_company',
        'appropriation.self.integer' => '01 appropriation_self',

    ];

    public function update($req, $id)
    {

        $hr = HumanResource::find($id);
        if (!$hr) {
            return Service::response('999', '', 'hr not exist');
        }

        try {
            $change_enrollment = '';
            if(!empty($req['change']['enrollment'])){
                $change_enrollment = $this->convertDateFormat($req['change']['enrollment']);
            }
            $change_withdrawal = '';
            if(!empty($req['change']['withdrawal'])){
                $change_withdrawal = $this->convertDateFormat($req['change']['withdrawal']);
            }
            $group_enrollment = '';
            if(!empty($req['group']['enrollment'])){
                $group_enrollment = $this->convertDateFormat($req['group']['enrollment']);
            }
            $group_withdrawal = '';
            if(!empty($req['group']['withdrawal'])){
                $group_withdrawal = $this->convertDateFormat($req['group']['withdrawal']);
            }
            $labor_health_enrollment = '';
            if(!empty($req['laborHealth']['enrollment'])){
                $labor_health_enrollment = $this->convertDateFormat($req['laborHealth']['enrollment']);
            }
            $labor_health_withdrawal = '';
            if(!empty($req['laborHealth']['withdrawal'])){
                $labor_health_withdrawal = $this->convertDateFormat($req['laborHealth']['withdrawal']);
            }

            $labor_amount = $req['labor']['amount'] ?? 0;
            $labor_deductible = $req['labor']['deductible'] ?? 0;
            $labor_pensio = $req['labor']['pensio'] ?? 0;
            $health_amount = $req['health']['amount'] ?? 0;
            $health_deductible = $req['health']['deductible'] ?? 0;
            $health_family = $req['health']['family'] ?? 0;
            $appropriation_company = $req['appropriation']['company'] ?? 0;
            $appropriation_self = $req['appropriation']['self'] ?? 0;

            HumanResourceInsurance::updateOrCreate(
                [
                    'hr_id' => $id,
                ],
                [
                    'change_enrollment' => $change_enrollment,
                    'change_withdrawal' => $change_withdrawal,
                    'group_enrollment' => $group_enrollment,
                    'group_withdrawal' => $group_withdrawal,
                    'labor_health_enrollment' => $labor_health_enrollment,
                    'labor_health_withdrawal' => $labor_health_withdrawal,
                    'labor_amount' => $labor_amount,
                    'labor_deductible' => $labor_deductible,
                    'labor_pensio' => $labor_pensio,
                    'health_amount' => $health_amount,
                    'health_deductible' => $health_deductible,
                    'health_family' => $health_family,
                    'appropriation_company' => $appropriation_company,
                    'appropriation_self' => $appropriation_self,
                ]
            );

            return Service::response('00', 'OK', '');


        } catch (\Exception $e) {
            $message  = 'Exception Message: '   . $e->getMessage();
            $message .= '<br>Exception Code: '  . $e->getCode();
            $message .= '<br>Exception String: ' . $e->__toString();

            return Service::response('999', '', $message);
        }
    }
}
