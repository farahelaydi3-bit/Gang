<?php

namespace App\Services;

use App\Models\Court;
use App\Models\OwnerWebsite;
use App\Models\OwnerWebsiteSection;
use App\Models\Sports;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Safe, data-backed HTML+CSS only. Gemini controls copy; owners select the fixed theme. */
class GangSiteBuilder
{
    public const THEMES = ['forest', 'sand', 'ocean', 'orange', 'midnight'];

    public function approvedSports(int $adminId)
    {
        return Sports::query()->whereHas('courts', function ($query) use ($adminId) {
            $query->where('admin_id', $adminId)->where('status', 'approved');
        })->orderBy('name')->get();
    }

    public function syncSections(OwnerWebsite $website): void
    {
        // Leave archived sections/images untouched. Only approved sports are shown publicly.
        foreach ($this->approvedSports($website->admin_id) as $sport) {
            OwnerWebsiteSection::firstOrCreate(
                ['owner_website_id' => $website->id, 'sport_id' => $sport->id],
                ['title' => $sport->name, 'sort_order' => $sport->id, 'images' => []]
            );
        }
    }

    public function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $base = $base === '' ? 'site' : Str::limit($base, 60, '');
        do {
            $slug = $base . '-' . strtolower(Str::random(7));
        } while (OwnerWebsite::where('slug', $slug)->exists());
        return $slug;
    }

    public function html(OwnerWebsite $website): string
    {
        $allowed = $this->approvedSports($website->admin_id)->pluck('id');
        $website->loadMissing('admin');
        $sections = $website->sections()->with('sport')->orderBy('sort_order')->orderBy('id')
            ->get()->filter(fn ($s) => $allowed->contains($s->sport_id));
        foreach ($sections as $section) {
            $courts = Court::query()->where('admin_id', $website->admin_id)
                ->where('sport_id', $section->sport_id)->where('status', 'approved')
                ->orderBy('name')->get();
            $section->setRelation('courts', $courts);
        }
        $phone = preg_replace('/[^0-9]/', '', (string) ($website->admin?->phone ?? ''));
        if (str_starts_with($phone, '00962')) $phone = substr($phone, 2);
        elseif (preg_match('/^07[0-9]{8}$/', $phone)) $phone = '962' . substr($phone, 1);
        $whatsappUrl = strlen($phone) >= 9 && strlen($phone) <= 15 ? 'https://wa.me/' . $phone : null;
        return view('site-studio.public', compact('website', 'sections', 'whatsappUrl'))->render();
    }

    public function saveHtml(OwnerWebsite $website): void
    {
        $path = 'site-studio/' . $website->id . '/index.html';
        Storage::disk('local')->put($path, $this->html($website));
        if ($website->html_path !== $path) $website->update(['html_path' => $path]);
    }
}
