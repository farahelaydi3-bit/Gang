<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Court extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'sport_id',
        'name',
        'address',
        'image',
        'opening_time',
        'closing_time',
        'min_booking_minutes',
        'weekday_price',
        'weekend_price',
        'evening_price',
        'deposit_amount',
        'status',
        'latitude',
        'longitude',
    ];

    public function sports()
    {
        return $this->belongsTo(Sports::class, 'sport_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'court_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function bundledPhotoUrl(): ?string
    {
        $image = $this->image;

        return $image && basename($image) === $image && is_file(public_path('courtbook-assets/images/' . $image))
            ? asset('courtbook-assets/images/' . $image)
            : null;
    }

    public function thumbnailUrl(): string
    {
        $bundled = $this->bundledPhotoUrl();
        if ($bundled !== null) {
            return $bundled;
        }

        $image = $this->image;
        if ($image && preg_match('~\A(?:images|courtbook-assets/images)/[^/\\\\]+\z~', $image) && is_file(public_path($image))) {
            return asset($image);
        }

        $uploaded = $this->uploadedPhotoPath();

        return $uploaded === null ? asset('courtbook-assets/images/hero-football.png') : asset('storage/' . $uploaded);
    }

    public function uploadedPhotoPath(): ?string
    {
        $path = $this->image;
        if (! $path || ! preg_match('~\Acourts/[a-z0-9_-]+\.(?:jpe?g|png|webp)\z~i', $path)) {
            return null;
        }

        return Storage::disk('public')->fileExists($path) ? $path : null;
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'court_id');
    }

    public function waitingList()
    {
        return $this->hasMany(WaitingList::class, 'court_id');
    }
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

}
