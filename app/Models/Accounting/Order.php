<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        // "invoice_number",
        "date",
        "time",
        "code",
        // $arr[4]外部訂單
        "source",
        "order_type",
        "charge",
        // $arr[8]數值簡化
        "discount",
        "total",
        "payment",
        "payment_info",
        "payment_memo",
        "invoice_type",
        "name",
        "phone",
        "meal_mome",
        //$arr[18]品項->寫入分表order_products
        "customer",
        "customer_phone",

        "user_id",
        "store_id",
        "import_source"
    ];
}
