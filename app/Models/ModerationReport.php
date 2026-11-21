<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModerationReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'court_id',
        'admin_id',
        'action',
        'ai_decision',
        'ai_reason',
        'notes',
        'reviewed_by_super_admin_id',
    ];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}