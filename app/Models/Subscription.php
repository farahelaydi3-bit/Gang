<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
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
    ];

    protected function casts(): array
    {
        return [
            'total_hours' => 'decimal:2',
            'carried_over_hours' => 'decimal:2',
            'used_hours' => 'decimal:2',
            'amount' => 'decimal:2',
            'duration_days' => 'integer',
            'start_date' => 'date',
            'end_date' => 'datetime',
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
        return $this->hasMany(Booking::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function previousSubscription()
    {
        return $this->belongsTo(
            Subscription::class,
            'previous_subscription_id'
        );
    }

    public function renewals()
    {
        return $this->hasMany(
            Subscription::class,
            'previous_subscription_id'
        );
    }
}
