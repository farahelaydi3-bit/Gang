<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OwnerWebsite extends Model
{
    protected $fillable = [
        'admin_id', 'name', 'slug', 'tagline', 'intro', 'theme', 'logo_path',
        'hero_image_path', 'maps_url', 'html_path', 'is_published', 'is_active', 'trial_started_at',
        'trial_ends_at', 'paid_until', 'ai_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean', 'is_active' => 'boolean',
            'trial_started_at' => 'datetime', 'trial_ends_at' => 'datetime',
            'paid_until' => 'datetime', 'ai_generated_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo { return $this->belongsTo(Admin::class); }
    public function sections(): HasMany { return $this->hasMany(OwnerWebsiteSection::class); }

    public function hasAccess(): bool
    {
        return (bool) ($this->is_active && $this->is_published
            && $this->admin?->status === 'active'
            && (($this->trial_ends_at && $this->trial_ends_at->isFuture())
                || ($this->paid_until && $this->paid_until->isFuture())));
    }
}
