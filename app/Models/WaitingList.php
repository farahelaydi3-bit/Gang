<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaitingList extends Model
{
    use HasFactory;

    protected $table = 'waiting_list';

    protected $fillable = [
        'user_id', 'court_id', 'booking_date', 'start_time', 'end_time',
        'status', 'notified_at', 'offer_expires_at',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'notified_at' => 'datetime',
        'offer_expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }
}
