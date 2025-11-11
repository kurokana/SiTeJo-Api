<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'nim_nip',
        'role',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    
    /**
     * Tickets yang dibuat oleh mahasiswa
     */
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'student_id');
    }

    /**
     * Tickets yang di-assign ke dosen
     */
    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'lecturer_id');
    }

    /**
     * History aktivitas user
     */
    public function ticketHistories()
    {
        return $this->hasMany(TicketHistory::class);
    }

    /**
     * Documents yang di-upload user
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    // Helper methods
    
    public function isMahasiswa()
    {
        return $this->role === 'mahasiswa';
    }

    public function isDosen()
    {
        return $this->role === 'dosen';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
