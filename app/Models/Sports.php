<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Courts;

class Sports extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function courts(){
        return $this->hasMany(Courts::class,'sport_id');
    }

    public function ownerApplications()
{
    return $this->belongsToMany(
        OwnerApplication::class,
        'owner_application_sport',
        'sport_id',
        'owner_application_id'
    );
}
}
