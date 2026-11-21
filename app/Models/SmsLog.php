<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsLog extends Model
{
	//
	use HasFactory;

	protected $fillable = [
		'user_id',
		'admin_id',
		'waiting_list_id',
		'phone',
		'message_type',
		'message',
		'status',
		'provider_message_id',
		'sent_at',
	];

	protected $casts = [
		'sent_at' => 'datetime',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function admin()
	{
		return $this->belongsTo(Admin::class);
	}

	public function waitingList()
	{
		return $this->belongsTo(WaitingList::class);
	}
}
