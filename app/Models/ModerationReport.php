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
        'pre_ban_state',
        'related_report_id',
        'reviewed_by_super_admin_id',
    ];

    protected $casts = [
        'pre_ban_state' => 'array',
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

    public function resolutions()
    {
        return $this->hasMany(self::class, 'related_report_id');
    }
}
