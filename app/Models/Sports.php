<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Court;

class Sports extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function courts()
    {
        return $this->hasMany(Court::class, 'sport_id');
    }
}