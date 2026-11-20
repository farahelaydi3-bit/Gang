<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerApplicationAttachment extends Model
{
    protected $fillable = [
        'owner_application_id',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function application()
    {
        return $this->belongsTo(
            OwnerApplication::class,
            'owner_application_id'
        );
    }
}
