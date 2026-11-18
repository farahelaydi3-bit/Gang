<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $fillable = [
        'super_admin_id',
        'name',
        'email',
        'phone',
        'business_name',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
}
