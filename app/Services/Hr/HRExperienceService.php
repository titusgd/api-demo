<?php

namespace App\Services\Hr;

use App\Services\Service;

use App\Models\Hr\HumanResource;
use App\Models\Hr\HumanResourceExperience;

/**
 * Class HRExperienceService.
 */
class HRExperienceService extends Service
{

    public $validationRules = [
        'seniority' => 'integer',
        'annualLeave' => 'integer',
        'startDate' => 'string',
        'endDate' => 'string',
        'introduction' => 'string',
    ];

    public $validationMsg = [
        'seniority.integer' => '01 seniority',
        'annualLeave.integer' => '01 annualLeave',
        'startDate.string' => '01 startDate',
        'endDate.string' => '01 endDate',
        'introduction.string' => '01 introduction',
    ];

    public function update($req, $id)
    {

        $hr = HumanResource::find($id);

        if (!$hr) {
            return Service::response('999', '', 'hr not exist');
        }

        $seniority = $req['seniority'] ?? 0;
        $annualLeave = $req['annualLeave'] ?? 0;
        $startDate = '';
        if (!empty($req['startDate'])) {
            $startDate = $this->convertDateFormat($req['startDate']);
        }
        $endDate = '';
        if (!empty($req['endDate'])) {
            $endDate = $this->convertDateFormat($req['endDate']);
        }
        $introduction = $req['introduction'] ?? '';

        try {

            HumanResourceExperience::updateOrCreate(
                [
                    'hr_id' => $id,
                ],
                [
                    'seniority' => $seniority,
                    'annual_leave' => $annualLeave,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'introduction' => $introduction,
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
