<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Review extends Model
{
    use HasFactory;
    protected $fillable = [
       'booking_id',
       'rating',
       'comment',
    ];

   protected function casts():array
{
return['rating'=>'integer']    ;


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
