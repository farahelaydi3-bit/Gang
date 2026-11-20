<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use App\Models\Sports;
// use App\Models\Reviews;

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

    public function sports(){
        return $this->belongsTo(Sports::class, 'sport_id');
    }
   
    public function reviews(){
        return $this->hasMany(Reviews::class);
    }

   
    
}
