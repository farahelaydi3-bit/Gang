<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourtReviewSummary extends Model
{
    protected $fillable = [
        'court_id',
        'total_reviews',
        'safe_reviews',
        'spam_reviews',
        'abusive_reviews',
        'average_rating',
        'summary',
        'top_complaints',
        'top_praises',
        'generated_at',
    ];

    protected $casts = [
        'average_rating' => 'decimal:2',
        'generated_at'   => 'datetime',
    ];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }
}