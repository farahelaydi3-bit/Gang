<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupGame extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'court_id',
        'title',
        'description',
        'skill_level',
        'position_slots',
        'game_date',
        'start_time',
        'end_time',
        'max_players',
        'player_price',
        'status',
    ];

    protected $casts = [
        'game_date'      => 'date',
        'position_slots' => 'array',
        'start_time'     => 'string',
        'end_time'       => 'string',
        'player_price'   => 'decimal:2',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function court()
    {
        return $this->belongsTo(Court::class, 'court_id');
    }

    public function players()
    {
        return $this->belongsToMany(User::class, 'game_players', 'group_game_id', 'user_id')->withPivot('status', 'joined_at', 'position')->withTimestamps();
    }

    public function joinedPlayers()
    {
        return $this->players()->wherePivot('status', 'joined');
    }
}