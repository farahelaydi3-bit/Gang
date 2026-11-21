<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Court;


class GroupGame extends Model
{
    use HasFactory;

 protected $fillable = [
        'admin_id',
        'court_id',
        'title',
        'description',
        'game_date',
        'start_time',
        'end_time',
        'max_players',
        'player_price',
        'status',
    ];

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

       public function court()
    {
        return $this->belongsTo(Court::class, 'court_id');
    }


}
