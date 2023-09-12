<?php

namespace App\Models\Kkday;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class KkdayOrderCustom extends Model
{
    use HasFactory;

    protected $fillable = [
        'kkday_order_id',
        'cus_type',
        'english_last_name',
        'english_first_name',
        'native_last_name',
        'native_first_name',
        'tel_country_code',
        'tel_number',
        'gender',
        'contact_app',
        'contact_app_account',
        'country_cities',
        'zipcode',
        'address',
        'nationality',
        'MTP_no',
        'id_no',
        'passport_no',
        'passport_expdate',
        'birth',
        'height',
        'height_unit',
        'weight',
        'weight_unit',
        'shoe,shoe_unit',
        'shoe_type',
        'glass_degree',
        'meal',
        'allergy_food',
        'have_app',
        'guide_lang'
    ];
}
