<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'status',];
}