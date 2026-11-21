<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    public function applications()
    {
        return $this->hasMany(OwnerApplication::class);
    }

    public function courts(): HasMany
    {
        return $this->hasMany(Court::class);
    }

    public function groupGames()
    {
        return $this->hasMany(GroupGame::class);
    }

    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }
}
