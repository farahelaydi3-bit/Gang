<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerWebsiteSection extends Model
{
    protected $fillable = ['owner_website_id', 'sport_id', 'title', 'description', 'images', 'sort_order'];

    protected function casts(): array { return ['images' => 'array']; }

    public function website(): BelongsTo { return $this->belongsTo(OwnerWebsite::class, 'owner_website_id'); }
    public function sport(): BelongsTo { return $this->belongsTo(Sports::class, 'sport_id'); }
}
