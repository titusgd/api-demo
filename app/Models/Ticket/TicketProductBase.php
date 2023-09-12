<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class TicketProductBase extends Model
{
    use HasFactory;

    protected $table = 'ticket_product_bases';

    protected $fillable = [
        'prod_no',
        'prod_name',
        'prod_url_no',
        'prod_type',
        'tag',
        'rating_count',
        'avg_rating_star',
        'instant_booking',
        'order_count',
        'days',
        'hours',
        'duration',
        'introduction',
        'prod_img_url',
        'b2c_price',
        'b2b_price',
        'prod_currency',
        'countries',
        'purchase_type',
        'purchase_date',
        'earliest_sale_date',
    ];
}
