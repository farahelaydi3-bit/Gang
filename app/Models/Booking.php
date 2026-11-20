<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Booking extends Model
{
    protected $fillable = [
        'court_id',
        'user_id',
        'created_by_admin_id',
        'subscription_id',
        'booking_type',
        'booking_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'total_price',
        'deposit_amount',
        'booking_status',
    ];
    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'duration_minutes' => 'integer',
            'total_price' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
        ];
    }

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function creatorAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function review(){
     return $this->hasOne(Review::class);

    }
    
    public function groupGame()
{
    return $this->hasOne(\App\Models\GroupGame::class);
}
}