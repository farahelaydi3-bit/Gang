<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use App\Models\Sports;
// use App\Models\Reviews;

class Courts extends Model
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

    public function sports(){
        return $this->belongsTo(Sports::class);
    }
   
    public function reviews(){
        return $this->hasMany(Reviews::class);
    }

    //  public function admin()
    // {
    //     return $this->belongsTo(Admin::class, 'admin_id');
    // }


    //  public function bookings()
    // {
    //     return $this->hasMany(Booking::class, 'court_id');
    // }

    //  public function waitingList()
    // {
    //     return $this->hasMany(WaitingList::class, 'court_id');
    // }
    
}
