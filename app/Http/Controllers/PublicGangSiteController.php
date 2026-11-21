<?php

namespace App\Http\Controllers;

use App\Models\OwnerWebsite;
use Illuminate\Support\Facades\Storage;

class PublicGangSiteController extends Controller
{
    public function show(string $slug)
    {
        $website = OwnerWebsite::with('admin')->where('slug', $slug)->firstOrFail();
        if (!$website->hasAccess() || !$website->html_path || !Storage::disk('local')->exists($website->html_path)) {
            return response()->view('site-studio.unavailable', [], 404)->header('Cache-Control', 'no-store');
        }
        return response(Storage::disk('local')->get($website->html_path))
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-store')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Content-Security-Policy', "default-src 'none'; style-src 'unsafe-inline'; img-src 'self' data:; font-src 'self'; base-uri 'none'; form-action 'none'; frame-ancestors 'self'");
    }
}
