<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'game_player_id',
        'payment_stage',
        'subscription_id',
        'payment_type',
        'payment_method',
        'amount',
        'status',
        'transaction_reference',
        'payment_date',
        'refund_status',
        'stripe_refund_id',
        'refund_amount_minor',
        'refund_currency',
        'refund_requested_at',
        'refunded_at',
        'refund_error',
    ];
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'datetime',
            'refund_requested_at' => 'datetime',
            'refunded_at' => 'datetime',
            'refund_amount_minor' => 'integer',
        ];
    }
    public function refundDisplayAmount(): ?string
    {
        if ($this->refund_amount_minor === null || !$this->refund_currency) {
            return null;
        }
        $currency = strtoupper($this->refund_currency);
        $decimals = in_array($currency, ['BIF','CLP','DJF','GNF','JPY','KMF','KRW','MGA','PYG','RWF','UGX','VND','VUV','XAF','XOF','XPF'], true) ? 0
            : (in_array($currency, ['BHD','JOD','KWD','OMR','TND'], true) ? 3 : 2);
        return number_format($this->refund_amount_minor / (10 ** $decimals), $decimals).' '.$currency;
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
