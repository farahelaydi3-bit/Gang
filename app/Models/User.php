<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'google_id',
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
            'phone_verified_at' => 'datetime',
        ];
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function groupGames()
    {
        return $this->belongsToMany(GroupGame::class, 'game_players', 'user_id', 'group_game_id')
            ->withPivot('status', 'joined_at', 'payment_id', 'reservation_expires_at')
            ->withTimestamps();
    }

    public function waitingList()
    {
        return $this->hasMany(WaitingList::class);
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }

    public function subscription()
    {
        return $this->hasMany(Subscription::class);
    }
}
