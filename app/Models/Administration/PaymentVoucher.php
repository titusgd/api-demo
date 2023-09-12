<?php

namespace App\Models\Administration;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class PaymentVoucher extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'store_id',
        'title',
        'content',
    ];

    public function payment_voucher_items()
    {
        return $this->hasMany('App\Models\Administration\PaymentVoucherItem');
    }

    public function reviews()
    {
        return $this->hasMany('App\Models\Administration\Review', 'fk_id', 'id');
    }

    public function users()
    {
        return $this->belongsTo('App\Models\Account\User');
    }
}
