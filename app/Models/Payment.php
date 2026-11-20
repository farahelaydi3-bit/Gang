<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'payment_stage',
        'subscription_id',
        'payment_type',
        'payment_method',
        'amount',
        'status',
        'transaction_reference',
        'payment_date',
    ];
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'datetime',
        ];
    }
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
