<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
		'booking_id',
		'user_id',
		'court_id',
		'rating',
		'comment',
		'ai_decision',
		'ai_reason',
		'ai_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
			'ai_checked_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

   public function court()
{
    return $this->belongsTo(Court::class);
}
}
