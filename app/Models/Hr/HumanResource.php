<?php

namespace App\Models\Hr;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

use App\Models\Hr\Organize;
use App\Models\Hr\Position;
use App\Models\Hr\HumanResourceAborigine;
use App\Models\Hr\HumanResourceCertificate;
use App\Models\Hr\HumanResourceEducation;
use App\Models\Hr\HumanResourceEmergencyContact;;

use App\Models\Hr\HumanResourceExperience;
use App\Models\Hr\HumanResourceForeignPhone;
use App\Models\Hr\HumanResourceIndividual;
use App\Models\Hr\HumanResourceInsurance;
use App\Models\Hr\HumanResourceOffice;
use App\Models\Hr\HumanResourceOther;
use App\Models\Hr\HumanResourcePosition;
use App\Models\Hr\HumanResourceTest;;

class HumanResource extends Model
{
    use HasFactory;

    protected $table = 'human_resources';

    protected $fillable = [
        'organize_id',
        'code',
        'chinese_name',
        'english_name',
        'rank_id',
        'flag'
    ];

    protected function get_detail($hr_id)
    {

        $hr = Self::find($hr_id);

        // 個人
        $individual = HumanResourceIndividual::where('hr_id', $hr_id)->first();
        $uid = $individual->uid ?? '';
        $birthday = $individual->birthday ?? '';
        $blood_type = $individual->blood_type ?? '';
        $phone = $individual->phone ?? '';
        $contact_tel = $individual->contact_tel ?? '';
        $contact_address = $individual->contact_address ?? '';
        $home_tel = $individual->home_tel ?? '';
        $home_address = $individual->home_address ?? '';
        $family = $individual->family ?? '';
        $note = $individual->note ?? '';

        // postition
        $position = Position::find($hr->position_id);
        $position_id = $position->id ?? '';
        $rank = $position->rank ?? '';
        $position_title = $position->name ?? '';

        // education
        $education = HumanResourceEducation::where('hr_id', $hr->id)->first();
        $education_highest = $education->highest ?? '';
        $education_department = $education->depaerment ?? '';

        // experience
        $experience = HumanResourceExperience::where('hr_id', $hr->id)->first();
        $seniority = $experience->seniority ?? '';
        $annualLeave = $experience->annual_leave ?? 0;
        $startDate = $experience->start_date ?? '';
        if ($startDate) {
            $startDate = str_replace('-', '/', $startDate);
        }
        $endDate = $experience->end_date ?? '';
        if ($endDate) {
            $endDate = str_replace('-', '/', $endDate);
        }
        $introduction = $experience->introduction ?? '';

        // office
        $office = HumanResourceOffice::where('hr_id', $hr->id)->first();
        $office_email = $office->email ?? '';
        $office_tel = $office->tel ?? '';
        $office_extension = $office->extension ?? '';
        $office_fax = $office->fax ?? '';

        // other
        $other = HumanResourceOther::where('hr_id', $hr->id)->first();
        $disability_identification = $other->disability_identification ?? false;
        $sales_performance = $other->sales_performance ?? false;
        $punch_in = $other->punch_in ?? false;
        $service_area_id = $other->service_area_id ?? 0;
        $service_area_code = $other->service_area_code ?? '';
        $service_area_name = $other->service_area_name ?? '';

        // test
        $test = HumanResourceTest::where('hr_id', $hr->id)->first();
        $dominance = $test->dominance ?? '';
        $influence = $test->influence ?? '';
        $steady = $test->steady ?? '';
        $caution = $test->caution ?? '';

        // insurance
        $insurance = HumanResourceInsurance::where('hr_id', $hr->id)->first();
        $change_enrollment = $insurance->change_enrollment ?? '';
        $change_withdrawal = $insurance->change_withdrawal ?? '';
        $group_enrollment = $insurance->group_enrollment ?? '';
        $group_withdrawal = $insurance->group_withdrawal ?? '';
        $labor_health_enrollment = $insurance->labor_health_enrollment ?? '';
        $labor_health_withdrawal = $insurance->labor_health_withdrawal ?? '';
        $labor_amount = $insurance->labor_amount ?? 0;
        $labor_deductible = $insurance->labor_deductible ?? 0;
        $labor_pensio = $insurance->labor_pensio ?? 0;
        $health_amount = $insurance->health_amount ?? 0;
        $health_deductible = $insurance->health_deductible ?? 0;
        $health_family = $insurance->health_family ?? 0;
        $appropriation_company = $insurance->appropriation_company ?? 0;
        $appropriation_self = $insurance->appropriation_self ?? 0;

        // emergencyContact
        $emr_contact = HumanResourceEmergencyContact::where('hr_id', $hr->id)->first();
        $emr_contact_name = $emr_contact->name ?? '';
        $emr_contact_relation = $emr_contact->relation ?? '';
        $emr_contact_mobile = $emr_contact->mobile ?? '';
        $emr_contact_tel = $emr_contact->tel ?? '';

        // 原住民
        $aborigine = HumanResourceAborigine::where('hr_id', $hr_id)->first();
        $aborigine_type = $aborigine->type ?? 0;
        $aborigine_name = $aborigine->name ?? '';

        $birthday = $individual->birthday ?? '';
        if ($birthday) {
            $birthday = str_replace('-', '/', $birthday);
        }

        $res = [
            'id' => $hr->id,
            'individual' => [
                'name' => [
                    "chinese" => $hr->chinese_name,
                    "english" => $hr->english_name
                ],
                'identityCard' => $uid,
                'birthday' => $birthday,
                'bloodType' => $blood_type,
                'phone' => $phone,
                'conact' => [
                    'tel' => $contact_tel,
                    'address' => $contact_address,
                ],
                'householdRegistration' => [
                    'tel' => $home_tel,
                    'address' => $home_address,
                ],
                'family' => $family,
                'note' => $note,

            ],
            'position' => [
                'id' => $position_id,
                'rank' => $rank,
                'title' => $position_title,
            ],
            'education' => [
                'highest' => $education_highest,
                'department' => $education_department,
            ],
            'experience' => [
                'seniority' => $seniority,
                'annualLeave' => $annualLeave,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'introduction' => $introduction,
            ],
            'office' => [
                'tel' => $office_tel,
                'email' => $office_email,
                'extension' => $office_extension,
                'fax' => $office_fax,
            ],
            'DISC' => [
                'D' => $dominance,
                'I' => $influence,
                'S' => $steady,
                'C' => $caution
            ],
            'other' => [
                'disabilityIdentification' => $disability_identification,
                'salesPerformance' => $sales_performance,
                'punchIn' => $punch_in,
                'serviceArea' => [
                    'id' => $service_area_id,
                    'code' => $service_area_code,
                    'name' => $service_area_name,
                ]
            ],
            'emergencyContact' => [
                'name' => $emr_contact_name,
                'relation' => $emr_contact_relation,
                'mobile' => $emr_contact_mobile,
                'tel' => $emr_contact_tel,
            ],
            'insurance' => [
                'change' => [
                    'enrollment' => $change_enrollment,
                    'withdrawal' => $change_withdrawal
                ],
                'group' => [
                    'enrollment' => $group_enrollment,
                    'withdrawal' => $group_withdrawal
                ],
                'laborHealth' => [
                    'enrollment' => $labor_health_enrollment,
                    'withdrawal' => $labor_health_withdrawal
                ],
                'labor' => [
                    "amount" => $labor_amount,
                    "deductible" => $labor_deductible,
                    'pensio' => $labor_pensio
                ],
                'health' => [
                    'amount' => $health_amount,
                    'deductible' => $health_deductible,
                    'family' => $health_family
                ],
                'appropriation' => [
                    'company' => $appropriation_company,
                    'self' => $appropriation_self,
                ],
            ],
            'aborigines' => [
                'type' => $aborigine_type,
                'name' => $aborigine_name,
            ]
        ];



        return $res;
    }

    protected function get_position_count($position_id)
    {

        $count = HumanResource::where('position_id', $position_id)->count();

        return $count;
    }
}
