<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class GamePlayer extends Model
{
    //
	use HasFactory;


	protected $fillable = [
		'group_game_id',
		'user_id',
		'status',
		'joined_at',
		'payment_id',
		'reservation_expires_at',
	];

	public function groupGame()
	{
		return $this->belongsTo(GroupGame::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function payments()
	{
		return $this->hasMany(Payment::class);
	}
}
