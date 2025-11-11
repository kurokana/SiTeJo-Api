<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'old_status',
        'new_status',
        'notes',
    ];

    // Relationships

    /**
     * Ticket yang di-track
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * User yang melakukan action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
