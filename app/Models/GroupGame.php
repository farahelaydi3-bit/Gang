<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GroupGame extends Model
{
    //
		use HasFactory;

	protected $fillable = [
		'admin_id',
		'booking_id',
		'title',
		'description',
		'max_players',
		'status',
	];

	// public function admin()
	// {
	// 	return $this->belongsTo(Admin::class);
	// }

	// public function booking()
	// {
	// 	return $this->belongsTo(Booking::class);
	// }

	public function players()
	{
		// Many-to-Many using as pivot game_players
		return $this->belongsToMany(User::class, 'game_players')->withPivot('status', 'joined_at')->withTimestamps();
	}

	public function joinedPlayers()
	{
		return $this->belongsToMany(User::class, 'game_players')->withPivot('status', 'joined_at')->withTimestamps()->wherePivot('status', 'joined');
	}

}
