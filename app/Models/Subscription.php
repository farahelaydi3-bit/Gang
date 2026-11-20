<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Notifications\SubscriptionLowBalance;

class Subscription extends Model
{
    protected $fillable=[ 'user_id',
        'admin_id',
        'previous_subscription_id',
        'package_name',
        'total_hours',
        'carried_over_hours',
        'used_hours',
        'amount',
        'duration_days',
        'start_date',
        'end_date',
        'status',
        'discount_percentage',
        'court_id',
'paid_at',
'activated_at',
'reserved_carryover_hours',
'carryover_finalized_at',
'low_balance_notified_at',
'expiry_reminder_sent_at',
];

        protected function casts():array
        {
return
[
'duration_days'=>'integer',
'total_hours'=>'decimal:2',
'carried_over_hours'=>'decimal:2',
'used_hours'=>'decimal:2',
'amount'=>'decimal:2',
'discount_percentage'=>	'decimal:2',
'start_date'=>'date',
'end_date'=>'datetime',
'low_balance_notified_at' => 'datetime',
'expiry_reminder_sent_at' => 'datetime',
'reserved_carryover_hours' => 'decimal:2',
'carryover_finalized_at' => 'datetime',
'paid_at' => 'datetime',
'activated_at' => 'datetime',
];
}
        public function user()
    {
    return $this->belongsTo(User::class);

    }

      public function admin()
    {
    return $this->belongsTo(Admin::class);

    }
      public function bookings()
    {
    return $this->hasMany(Booking ::class);


    }
    

    public function payment()
    {
    return $this->hasOne(Payment::class);

    }

public function previousSubscription()
{
    return $this->belongsTo(Subscription::class, 'previous_subscription_id');
}

public function renewals()
{
    return $this->hasMany(Subscription::class, 'previous_subscription_id');
}
public function court()
{
    return $this->belongsTo(Court::class);
}

public function getRemainingHoursAttribute(): float
{
    return max(
        0,
        (float) $this->total_hours
        + (float) $this->carried_over_hours
        - (float) $this->used_hours
        - (float) $this->reserved_carryover_hours
    );
}


public function notifyIfLowBalance(): void
{
    if ($this->status !== 'active') {
        return;
    }

    if ($this->low_balance_notified_at !== null) {
        return;
    }

    $total = (float) $this->total_hours
        + (float) $this->carried_over_hours;

    if ($total <= 0) {
        return;
    }

    if ($this->remaining_hours > $total * 0.25) {
        return;
    }

    $updated = static::query()
        ->whereKey($this->id)
        ->whereNull('low_balance_notified_at')
        ->update([
            'low_balance_notified_at' => now(),
        ]);

    if ($updated) {
        $this->refresh();
        $this->user->notify(
            new SubscriptionLowBalance($this)
        );
    }
}

}






