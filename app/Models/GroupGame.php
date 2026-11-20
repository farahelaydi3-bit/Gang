<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupGame extends Model
{
    use HasFactory;

    protected $fillable = ['admin_id', 'booking_id', 'title', 'description', 'max_players', 'status'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function players()
    {
        return $this->belongsToMany(User::class, 'game_players', 'group_game_id', 'user_id')
            ->withPivot('status', 'joined_at')->withTimestamps();
    }

    public function joinedPlayers()
    {
        return $this->players()->wherePivot('status', 'joined');
    }
}
