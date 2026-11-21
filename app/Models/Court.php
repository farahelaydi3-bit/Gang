<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Court extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'sport_id',
        'name',
        'address',
        'image',
        'opening_time',
        'closing_time',
        'min_booking_minutes',
        'weekday_price',
        'weekend_price',
        'evening_price',
        'deposit_amount',
        'status',
    ];

    public function sports()
    {
        return $this->belongsTo(Sports::class, 'sport_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'court_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'court_id');
    }

    public function waitingList()
    {
        return $this->hasMany(WaitingList::class, 'court_id');
    }
    public function subscriptions()
{
    return $this->hasMany(Subscription::class);
}
}