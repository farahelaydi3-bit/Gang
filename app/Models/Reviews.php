<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Reviews extends Model
{
    use HasFactory;
    protected $fillable = [
       'booking_id',
       'user_id',
       'court_id',
       'rating',
       'comment',
    ];

    public function users(){
        return $this->belongsTo(User::class);
    }

    public function courts()
    {
        return $this->belongsTo(Courts::class, 'court_id');
    
    public function booking(){
        return $this->belongsTo(Booking::class);
    }
}
