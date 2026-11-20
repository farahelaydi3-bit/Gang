<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerApplication extends Model
{
    protected $fillable = [
        'admin_id',
        'facility_name',
        'address',
        'courts_count',
        'facility_description',
        'application_message',
        'status',
        'review_notes',
        'reviewed_by',
        'submitted_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function sports()
    {
        return $this->belongsToMany(
            Sports::class,
            'owner_application_sport',
            'owner_application_id',
            'sport_id'
        );
    }

    public function attachments()
    {
        return $this->hasMany(
            OwnerApplicationAttachment::class
        );
    }

    public function reviewer()
    {
        return $this->belongsTo(
            SuperAdmin::class,
            'reviewed_by'
        );
    }
}
