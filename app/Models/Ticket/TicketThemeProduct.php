<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;


class TicketThemeProduct extends Model
{
    use HasFactory;

    protected $table = 'ticket_theme_products';

    protected $fillable = [
        'theme_id',
        'prod_no',
    ];
}
