<?php

namespace App\Models\Ticket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\ConnModel as Model;

class TicketTheme extends Model
{
    use HasFactory;

    protected $table = 'ticket_themes';

    protected $fillable = [
        'name',
        'status',
        'sort'
    ];
}
