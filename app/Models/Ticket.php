<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'student_id',
        'lecturer_id',
        'title',
        'description',
        'type',
        'status',
        'priority',
        'admin_notes',
        'lecturer_notes',
        'rejection_reason',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'completed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships

    /**
     * Mahasiswa yang mengajukan ticket
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Dosen yang dituju
     */
    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    /**
     * Dokumen yang terlampir
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * History perubahan ticket
     */
    public function histories()
    {
        return $this->hasMany(TicketHistory::class);
    }

    // Accessors & Mutators

    /**
     * Generate ticket number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (!$ticket->ticket_number) {
                $ticket->ticket_number = self::generateTicketNumber();
            }
        });
    }

    /**
     * Generate unique ticket number
     */
    public static function generateTicketNumber()
    {
        $date = now()->format('Ymd');
        $lastTicket = self::whereDate('created_at', now())->latest('id')->first();
        
        $sequence = $lastTicket ? (int) substr($lastTicket->ticket_number, -4) + 1 : 1;
        
        return 'TKT-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // Helper methods

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isInReview()
    {
        return $this->status === 'in_review';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }
}
