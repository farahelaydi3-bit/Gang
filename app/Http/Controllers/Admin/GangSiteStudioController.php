<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OwnerWebsite;
use App\Models\OwnerWebsiteSection;
use App\Services\GangSiteBuilder;
use App\Services\GangSiteGemini;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class GangSiteStudioController extends Controller
{
    private function mine(): OwnerWebsite
    {
        return OwnerWebsite::where('admin_id', auth('admin')->id())->firstOrFail();
    }

    public function index(GangSiteBuilder $builder)
    {
        $website = OwnerWebsite::where('admin_id', auth('admin')->id())->first();
        $sports = $builder->approvedSports(auth('admin')->id());
        if ($website) {
            $builder->syncSections($website);
            $website->load(['sections' => fn ($q) => $q->orderBy('sort_order'), 'sections.sport']);
        }
        return view('admins.site-studio.index', compact('website', 'sports'));
    }

    public function store(Request $request, GangSiteBuilder $builder)
    {
        if (OwnerWebsite::where('admin_id', auth('admin')->id())->exists()) {
            return redirect()->route('admin.site-studio.index')->with('error', __('You already have a website.'));
        }
        if ($builder->approvedSports(auth('admin')->id())->isEmpty()) {
            throw ValidationException::withMessages(['name' => __('You need at least one approved court to create a website.')]);
        }
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:180'],
            'intro' => ['nullable', 'string', 'max:1600'],
            'theme' => ['required', Rule::in(GangSiteBuilder::THEMES)],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'maps_url' => ['nullable', 'string', 'url', 'max:2048'],
        ]);
        $this->validateMapsUrl($request, $data['maps_url'] ?? null);
        $website = DB::transaction(function () use ($data, $builder) {
            $website = OwnerWebsite::create([
                'admin_id' => auth('admin')->id(), 'name' => $data['name'],
                'slug' => $builder->uniqueSlug($data['name']),
                'tagline' => $data['tagline'] ?? '', 'intro' => $data['intro'] ?? '',
                'theme' => $data['theme'], 'maps_url' => $data['maps_url'] ?? null, 'is_active' => true, 'is_published' => false,
            ]);
            $builder->syncSections($website);
            return $website;
        });
        if ($request->hasFile('logo')) {
            $website->update(['logo_path' => $request->file('logo')->store('site-studio/' . $website->id . '/logo', 'public')]);
        }
        if ($request->hasFile('hero_image')) {
            $website->update(['hero_image_path' => $request->file('hero_image')->store('site-studio/' . $website->id . '/hero', 'public')]);
        }
        return redirect()->route('admin.site-studio.index')->with('success', __('Website created. Add images, then preview or publish.'));
    }

    public function update(Request $request, GangSiteBuilder $builder)
    {
        $website = $this->mine();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:180'],
            'intro' => ['nullable', 'string', 'max:1600'],
            'theme' => ['required', Rule::in(GangSiteBuilder::THEMES)],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'maps_url' => ['nullable', 'string', 'url', 'max:2048'],
        ]);
        $this->validateMapsUrl($request, $data['maps_url'] ?? null, $website->maps_url);
        $website->update(collect($data)->only(['name', 'tagline', 'intro', 'theme', 'maps_url'])->all());
        if ($request->hasFile('logo')) {
            $old = $website->logo_path;
            $website->update(['logo_path' => $request->file('logo')->store('site-studio/' . $website->id . '/logo', 'public')]);
            if ($old) Storage::disk('public')->delete($old);
        }
        if ($request->hasFile('hero_image')) {
            $oldHero = $website->hero_image_path;
            $website->update(['hero_image_path' => $request->file('hero_image')->store('site-studio/' . $website->id . '/hero', 'public')]);
            if ($oldHero) Storage::disk('public')->delete($oldHero);
        }
        $this->refreshPublished($website, $builder);
        return back()->with('success', __('Website settings saved.'));
    }

/** Only official Google Maps sharing links; confirmation means owner checked it, not geolocation verification. */
private function validateMapsUrl(Request $request, ?string $url, ?string $existing = null): void
{
    if (!$url) return;

    $parts = parse_url($url);
    $host = strtolower($parts['host'] ?? '');
    $path = $parts['path'] ?? '';
    $scheme = strtolower($parts['scheme'] ?? '');
    $googleHost = preg_match('/^(?:www\.|maps\.)?google\.(?:com|[a-z]{2,3}|com\.[a-z]{2})$/', $host) === 1;
    $valid = $scheme === 'https' && (
        ($googleHost && (str_starts_with($path, '/maps') || str_starts_with($path, '/maps/')))
        || ($host === 'maps.app.goo.gl' && trim($path, '/') !== '')
        || ($host === 'goo.gl' && str_starts_with($path, '/maps/'))
    );
    if (!$valid) {
        throw ValidationException::withMessages(['maps_url' => __('Use an HTTPS share link from Google Maps.')]);
    }
    if (!$request->boolean('maps_confirm')) {
        throw ValidationException::withMessages(['maps_confirm' => __('Confirm the Google Maps link points to your facility before saving.')]);
    }
}

    private function ownedApprovedSection(int $sectionId, GangSiteBuilder $builder): OwnerWebsiteSection
    {
        $website = $this->mine();
        $section = $website->sections()->findOrFail($sectionId);
        if (!$builder->approvedSports($website->admin_id)->pluck('id')->contains($section->sport_id)) abort(403);
        return $section;
    }

    public function updateSection(Request $request, int $section, GangSiteBuilder $builder)
    {
        $item = $this->ownedApprovedSection($section, $builder);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1600'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        $current = $item->images ?? [];
        $newImages = $request->file('images', []);
        if (count($current) + count($newImages) > 5) {
            throw ValidationException::withMessages(['images' => __('Maximum 5 photos per sport. Delete an image before adding another.')]);
        }
        $added = [];
        foreach ($newImages as $file) {
            $added[] = $file->store('site-studio/' . $item->owner_website_id . '/sport-' . $item->sport_id, 'public');
        }
        $item->update([
            'title' => $data['title'], 'description' => $data['description'] ?? '',
            'sort_order' => $data['sort_order'], 'images' => array_merge($current, $added),
        ]);
        $this->refreshPublished($item->website, $builder);
        return back()->with('success', __('Sport section updated.'));
    }

    public function deleteImage(Request $request, int $section, GangSiteBuilder $builder)
    {
        $item = $this->ownedApprovedSection($section, $builder);
        $data = $request->validate(['index' => ['required', 'integer', 'min:0', 'max:4']]);
        $images = $item->images ?? [];
        if (!array_key_exists($data['index'], $images)) abort(404);
        $path = $images[$data['index']];
        array_splice($images, $data['index'], 1);
        $item->update(['images' => $images]);
        Storage::disk('public')->delete($path);
        $this->refreshPublished($item->website, $builder);
        return back()->with('success', __('Photo removed.'));
    }

    public function generate(GangSiteGemini $gemini, GangSiteBuilder $builder)
    {
        $website = $this->mine();
        $builder->syncSections($website);
        $allowed = $builder->approvedSports($website->admin_id)->pluck('id');
        $sections = $website->sections()->with('sport')->whereIn('sport_id', $allowed)->get();
        if ($sections->isEmpty()) return back()->with('error', __('No approved sports available yet.'));
        $context = $sections->map(fn ($s) => [
            'sport_id' => $s->sport_id, 'sport_name' => $s->sport->name,
            'current_title' => $s->title, 'current_description' => $s->description,
        ])->all();
        try {
            $result = $gemini->generate($website, $context);
        } catch (Throwable $e) {
            Log::warning('Gang Site Studio AI failed', ['owner_website_id' => $website->id, 'error' => $e->getMessage()]);
            return back()->with('error', __('AI generation failed: check Gemini configuration or quota, and retry.'));
        }
        DB::transaction(function () use ($website, $sections, $result) {
            $website->update([
                'tagline' => $result['tagline'] ?: $website->tagline,
                'intro' => $result['intro'] ?: $website->intro,
                'ai_generated_at' => now(),
            ]);
            foreach ($sections as $section) {
                if (!isset($result['sections'][$section->sport_id])) continue;
                $text = $result['sections'][$section->sport_id];
                $section->update([
                    'title' => $text['title'] ?: $section->title,
                    'description' => $text['description'] ?: $section->description,
                ]);
            }
        });
        $this->refreshPublished($website, $builder);
        return back()->with('success', __('Website copy updated. Your selected theme was preserved.'));
    }

    public function preview(GangSiteBuilder $builder)
    {
        return response($builder->html($this->mine()))->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Cache-Control', 'no-store')->header('Content-Security-Policy', $this->csp());
    }

    public function publish(GangSiteBuilder $builder)
    {
        $website = $this->mine();
        if ($builder->approvedSports($website->admin_id)->isEmpty()) return back()->with('error', __('At least one approved court is required.'));
        if (!$website->trial_started_at) {
            $website->update(['trial_started_at' => now(), 'trial_ends_at' => now()->addMonthsNoOverflow(6)]);
        }
        $builder->saveHtml($website);
        $website->update(['is_published' => true]);
        return back()->with('success', __('Site published. Your 6-month free trial begins at first publication.'));
    }

    public function unpublish()
    {
        $this->mine()->update(['is_published' => false]);
        return back()->with('success', __('Website unpublished.'));
    }

    private function refreshPublished(OwnerWebsite $website, GangSiteBuilder $builder): void
    {
        if ($website->is_published) $builder->saveHtml($website->fresh());
    }

    private function csp(): string
    {
        return "default-src 'none'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; base-uri 'none'; form-action 'none'; frame-ancestors 'self'";
    }
}
