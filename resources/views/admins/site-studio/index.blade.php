
@extends('admins.layout.app')

@section('title', 'Gang Site Studio')
@section('page-title', 'Gang Site Studio')

@php
    $themes = [
        'forest' => 'White + green | Football',
        'sand' => 'White + sand | Tennis',
        'ocean' => 'White + navy | Badminton and padel',
        'orange' => 'White + orange | Basketball',
        'midnight' => 'White + grey | Classic',
    ];
@endphp

@push('styles')
<style>
    /* =========================
       Main Layout
    ========================= */

    .studio {
        max-width: none;
        margin: 0 auto;
        color: #24324a;
    }

    .studio-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .studio-panel {
        margin-bottom: 18px;
        padding: 22px;
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(15, 30, 50, 0.035);
        overflow-wrap: anywhere;
    }

    .studio-panel h3 {
        margin: 0 0 16px;
        color: #15253d;
        font-size: 21px;
    }

    .studio-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 15px;
    }

    /* =========================
       Hero
    ========================= */







    /* =========================
       Text & Status
    ========================= */

    .studio-hint {
        margin: 8px 0 16px;
        color: #64748b;
        font-size: 14px;
        line-height: 1.9;
    }

    .studio-pill,
    .studio-number {
        display: inline-block;
        padding: 7px 12px;
        border-radius: 100px;
        font-size: 13px;
        font-weight: 700;
    }

    .studio-pill {
        background: #e5f9ee;
        color: #236448;
    }


.studio-number {
        background: #FAF2E8;
        color: #89501D;
    }

    .studio-url {
        display: block;
        margin-top: 12px;
        color: #17635c;
        font-size: 14px;
        word-break: break-all;
    }

    /* =========================
       Forms
    ========================= */

    .studio-form {
        display: grid;
        gap: 20px;
    }

    .studio-field {
        display: flex;
        flex-direction: column;
        gap: 9px;
        min-width: 0;
    }

    .studio-field label {
        color: #43516b;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.7;
    }

    .studio-form input:not([type="checkbox"]),
    .studio-form select,
    .studio-form textarea {
        width: 100%;
        min-height: 48px;
        max-width: 100%;
        box-sizing: border-box;

        padding: 11px 13px;
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        border-radius: 10px;

        color: #1e293b;
        font: inherit;
        font-size: 15px;
        line-height: 1.6;
    }

    .studio-form textarea {
        min-height: 110px;
        resize: vertical;
    }

    .studio-form input:focus,
    .studio-form select:focus,
    .studio-form textarea:focus {
        outline: 2px solid #D4AE86;
        outline-offset: 1px;
        border-color: var(--admin-primary);
    }

    .studio-form input[type="file"] {
        padding: 10px;
        background: #f8fafc;
        cursor: pointer;
    }

    .studio-form input[type="file"]::file-selector-button {
        margin-inline-end: 12px;
        padding: 7px 12px;
        background: #F3E9DC;
        border: 1px solid var(--admin-border);
        border-radius: 8px;
        color: var(--admin-text);
        cursor: pointer;
    }

    /* =========================
       Maps Confirmation
    ========================= */

    .studio-check {
        display: flex;
        align-items: flex-start;
        gap: 12px;

        padding: 14px;
        background: #FAF2E8;
        border: 1px solid var(--admin-border);
        border-radius: 12px;

        color: #43516b;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.8;
        cursor: pointer;
    }

    .studio-check input[type="checkbox"] {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        margin: 4px 0 0;
        accent-color: var(--admin-primary);
        cursor: pointer;
    }

    .studio-check.is-invalid {
        border-color: #dc2626;
        background: #fff1f2;
    }

    .studio-validation-message {
        display: none;
        margin: 0;
        color: #b91c1c;
        font-size: 13px;
    }

    .studio-validation-message.is-visible {
        display: block;
    }

    /* =========================
       Buttons
    ========================= */










    .studio-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    /* =========================
       Alerts
    ========================= */





.studio-empty {
        padding: 25px;
        background: #fff8e9;
        color: #825817;
        border-radius: 13px;
        text-align: center;
    }

    /* =========================
       Images
    ========================= */

    .studio-cover-thumb {
        display: block;
        width: 100%;
        height: 220px;
        object-fit: contain;

        background: #f8fafc;
        border: 1px solid var(--admin-border);
        border-radius: 12px;
    }

    .studio-photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 14px;
        margin-top: 18px;
    }

    .studio-photo {
        display: flex;
        flex-direction: column;
        gap: 10px;
        min-width: 0;

        padding: 10px;
        background: var(--admin-surface);
        border: 1px solid var(--admin-border);
        border-radius: 12px;
    }

    .studio-photo img {
        display: block;
        width: 100%;
        height: 150px;
        object-fit: contain;

        background: #f8fafc;
        border-radius: 8px;
    }

    .studio-photo form {
        margin-top: auto;
    }

    .studio-photo button {
        width: 100%;
        padding: 8px 10px;
        background: #fff7f7;
        border: 1px solid #fecaca;
        border-radius: 7px;
        color: #b91c1c;
        cursor: pointer;
    }

    /* =========================
       Sport Sections & Preview
    ========================= */

    .studio-sport {
        margin-bottom: 16px;
        padding: 20px;
        background: #fbfdff;
        border: 1px solid #e6edf3;
        border-radius: 15px;
    }

    .studio-sport h4 {
        margin: 0;
        font-size: 19px;
    }

    .studio-preview {
        width: 100%;
        height: 500px;
        background: var(--admin-surface);
        border: 1px solid #d7e0ea;
        border-radius: 14px;
    }

    /* =========================
       Responsive
    ========================= */

    @media (max-width: 840px) {
        .studio-grid {
            grid-template-columns: 1fr;
        }

        .studio-panel {
            padding: 18px;
        }


.studio-preview {
            height: 380px;
        }
    }

    @media (max-width: 520px) {
        .studio-photo-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .studio-photo img {
            height: 120px;
        }


}
</style>
@endpush

@section('content')

<div class="studio" lang="en" dir="ltr">

    {{-- =========================================
         Hero
    ========================================= --}}

    <x-super-admin.page-header title="Gang Site Studio" description="Build and manage your facility website and connect visitors to court bookings." />
    <x-super-admin.form-errors />

    @if (!$website)

        <section class="admin-card studio-panel">
            <h3>Create a website</h3>

            <p class="studio-hint">
                Create one website for your facility. Sports sections are generated from your approved courts.
            </p>

            @if ($sports->isEmpty())

                <div class="studio-empty">
                    You need an approved court before you can create a website.
                </div>

            @else

                <p class="studio-hint">
                    Available sports:
                    {{ $sports->pluck('name')->join(' · ') }}
                </p>

                <form
                    class="studio-form"
                    action="{{ route('admin.site-studio.store') }}"
                    method="POST"
                    enctype="multipart/form-data"
                >
                    @csrf

                    <div class="studio-field">
                        <label for="create-name">
                            Website name
                        </label>

                        <input
                            id="create-name"
                            type="text"
                            name="name"
                            value="{{ old('name', auth('admin')->user()?->business_name) }}"
                            maxlength="120"
                            placeholder="MO HUB"
                            required
                        >
                    </div>

                    <div class="studio-field">
                        <label for="create-tagline">
                            Tagline (optional)
                        </label>

                        <input
                            id="create-tagline"
                            type="text"
                            name="tagline"
                            maxlength="180"
                            value="{{ old('tagline') }}"
                            placeholder="Your local sports destination"
                        >
                    </div>

                    <div class="studio-field">
                        <label for="create-intro">
                            About the facility (optional)
                        </label>

                        <textarea
                            id="create-intro"
                            name="intro"
                            maxlength="1600"
                        >{{ old('intro') }}</textarea>
                    </div>

                    <div class="studio-field">
                        <label for="create-theme">
                            Website theme
                        </label>

                        <select
                            id="create-theme"
                            name="theme"
                            required
                        >
                            @foreach ($themes as $key => $name)
                                <option
                                    value="{{ $key }}"
                                    @selected(old('theme', 'forest') === $key)
                                >
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="studio-field">
                        <label for="create-logo">
                            Website logo (optional)
                        </label>

                        <input
                            id="create-logo"
                            type="file"
                            name="logo"
                            accept="image/jpeg,image/png,image/webp"
                        >

                        <p class="studio-hint">
                            JPG, PNG or WEBP, up to 4 MB.
                        </p>
                    </div>

                    <div class="studio-field">
                        <label for="create-hero">
                            Cover photo (optional)
                        </label>

                        <input
                            id="create-hero"
                            type="file"
                            name="hero_image"
                            accept="image/jpeg,image/png,image/webp"
                        >

                        <p class="studio-hint">
                            Shown at the top of your website. Maximum 6 MB.
                        </p>
                    </div>

                    <div class="studio-field">
                        <label for="create-maps">
                            Facility link on Google Maps (optional)
                        </label>

                        <input
                            id="create-maps"
                            type="url"
                            name="maps_url"
                            data-studio-maps
                            dir="ltr"
                            maxlength="2048"
                            placeholder="https://maps.app.goo.gl/..."
                            value="{{ old('maps_url') }}"
                        >
                    </div>

                    <div>
                        <label class="studio-check">
                            <input
                                type="checkbox"
                                name="maps_confirm"
                                value="1"
                                data-studio-confirm
                                @checked(old('maps_confirm'))
                            >

                            <span>
                                I confirm the link points to my facility.
                            </span>
                        </label>

                        <p
                            class="studio-validation-message"
                            data-studio-map-error
                            role="alert"
                        >
                            Confirm the facility location before saving.
                        </p>
                    </div>

                    <button type="submit" class="admin-button">
                        Create website
                    </button>
                </form>

            @endif
        </section>

    @else

        {{-- =====================================
             Website Status
        ===================================== --}}

        <section class="admin-card studio-panel studio-row">
            <div>
                <h3>{{ $website->name }}</h3>

                <x-dashboard-status :state="$website->is_published ? 'active' : 'draft'"
                    :label="$website->is_published ? 'Published' : 'Draft'" />
                <x-dashboard-status :state="$website->is_active ? 'active' : 'inactive'"
                    :label="$website->is_active ? 'Active' : 'Disabled by an administrator'" />

                <p class="studio-hint">
                    @if ($website->trial_ends_at)
                        Trial ends:
                        {{ $website->trial_ends_at->format('Y-m-d') }}
                    @else
                        The free trial starts when you first publish your website.
                    @endif

                    @if ($website->paid_until)
                        <br>
                        Paid through:
                        {{ $website->paid_until->format('Y-m-d') }}
                    @endif
                </p>

                @if ($website->is_published)
                    <a
                        class="studio-url"
                        href="{{ route('gang.sites.show', $website->slug) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {{ route('gang.sites.show', $website->slug) }}
                    </a>
                @endif
            </div>

            <div class="studio-actions">
                @if ($website->is_published)
                    <form
                        action="{{ route('admin.site-studio.unpublish') }}"
                        method="POST"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="admin-button admin-button-outline"
                        >
                            Unpublish website
                        </button>
                    </form>
                @else
                    <form
                        action="{{ route('admin.site-studio.publish') }}"
                        method="POST"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="admin-button"
                        >
                            Publish website
                        </button>
                    </form>
                @endif
            </div>
        </section>

        <div class="studio-grid">

            {{-- =================================
                 Left Column
            ================================= --}}

            <div>

                {{-- Website Settings --}}

                <section class="admin-card studio-panel">
                    <h3>Website settings</h3>

                    <form
                        class="studio-form"
                        action="{{ route('admin.site-studio.update') }}"
                        method="POST"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        @method('PUT')

                        <div class="studio-field">
                            <label for="edit-name">
                                Website name
                            </label>

                            <input
                                id="edit-name"
                                type="text"
                                name="name"
                                maxlength="120"
                                value="{{ old('name', $website->name) }}"
                                required
                            >
                        </div>

                        <div class="studio-field">
                            <label for="edit-tagline">
                                Tagline
                            </label>

                            <input
                                id="edit-tagline"
                                type="text"
                                name="tagline"
                                maxlength="180"
                                value="{{ old('tagline', $website->tagline) }}"
                            >
                        </div>

                        <div class="studio-field">
                            <label for="edit-intro">
                                Website introduction
                            </label>

                            <textarea
                                id="edit-intro"
                                name="intro"
                                maxlength="1600"
                            >{{ old('intro', $website->intro) }}</textarea>
                        </div>

                        <div class="studio-field">
                            <label for="edit-theme">
                                Website theme
                            </label>

                            <select
                                id="edit-theme"
                                name="theme"
                                required
                            >
                                @foreach ($themes as $key => $name)
                                    <option
                                        value="{{ $key }}"
                                        @selected(old('theme', $website->theme) === $key)
                                    >
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="studio-field">
                            <label for="edit-logo">
                                Replace logo
                            </label>

                            <input
                                id="edit-logo"
                                type="file"
                                name="logo"
                                accept="image/jpeg,image/png,image/webp"
                            >
                        </div>

                        <div class="studio-field">
                            <label for="edit-hero">
                                Cover photo
                            </label>

                            <input
                                id="edit-hero"
                                type="file"
                                name="hero_image"
                                accept="image/jpeg,image/png,image/webp"
                            >

                            <p class="studio-hint">
                                Maximum 6 MB.
                            </p>

                            @if ($website->hero_image_path)
                                <img
                                    class="studio-cover-thumb"
                                    src="{{ '/storage/' . ltrim($website->hero_image_path, '/') }}"
                                    alt="Current cover photo"
                                    loading="lazy"
                                >
                            @endif
                        </div>

                        <div class="studio-field">
                            <label for="edit-maps">
                                Facility link on Google Maps
                            </label>

                            <input
                                id="edit-maps"
                                type="url"
                                name="maps_url"
                                data-studio-maps
                                dir="ltr"
                                maxlength="2048"
                                placeholder="https://maps.app.goo.gl/..."
                                value="{{ old('maps_url', $website->maps_url) }}"
                            >
                        </div>

                        <div>
                            <label class="studio-check">
                                <input
                                    type="checkbox"
                                    name="maps_confirm"
                                    value="1"
                                    data-studio-confirm
                                    @checked(old('maps_confirm'))
                                >

                                <span>
                                    I confirm the link points to my facility.
                                </span>
                            </label>

                            <p
                                class="studio-validation-message"
                                data-studio-map-error
                                role="alert"
                            >
                                Confirm the facility location before saving.
                            </p>
                        </div>

                        <button
                            type="submit"
                            class="admin-button"
                        >
                            Save changes
                        </button>
                    </form>
                </section>

                {{-- Gemini --}}

                <section class="admin-card studio-panel">
                    <h3>AI copywriting</h3>

                    <p class="studio-hint">
                        Use Gemini to write the tagline, introduction, and descriptions for approved sports. Your theme, colours, and photos stay the same.
                    </p>

                    <form
                        class="studio-form"
                        action="{{ route('admin.site-studio.generate') }}"
                        method="POST"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="admin-button"
                        >
                            Generate new copy
                        </button>
                    </form>
                </section>
            </div>

            {{-- =================================
                 Right Column
            ================================= --}}

            <div>

                {{-- Preview --}}

                <section class="admin-card studio-panel">
                    <div class="studio-row">
                        <h3>Website preview</h3>

                        <a
                            class="admin-button admin-button-outline"
                            href="{{ route('admin.site-studio.preview') }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Open preview
                        </a>
                    </div>

                    <iframe
                        class="studio-preview"
                        title="Sports website preview"
                        loading="lazy"
                        src="{{ route('admin.site-studio.preview') }}"
                    ></iframe>

                    <p class="studio-hint">
                        The published website updates when you save. Refresh the page to see the latest preview.
                    </p>
                </section>

                {{-- Subscription --}}

                <section class="admin-card studio-panel">
                    <h3>Subscription policy</h3>

                    <p class="studio-hint">
                        The six-month free trial starts when you first publish. After that, the website costs 5 JOD per month. An administrator renews it after confirming payment. Online payment is currently unavailable.
                    </p>
                </section>
            </div>
        </div>

        {{-- =====================================
             Sport Sections
        ===================================== --}}

        <section class="admin-card studio-panel">

            <div class="studio-row">
                <h3>Sport sections</h3>

                <span class="studio-number">
                    Up to 5 photos per sport
                </span>
            </div>

            <p class="studio-hint">
                These sections come from your approved courts. You can edit their titles, descriptions, order, and photos. Each court gets a booking link automatically.
            </p>

            @forelse (
                $website->sections->filter(
                    fn ($section) => $sports->contains('id', $section->sport_id)
                ) as $section
            )

                <article class="studio-sport">

                    <div class="studio-row">
                        <h4>{{ $section->sport->name }}</h4>

                        <span class="studio-number">
                            {{ count($section->images ?? []) }} / 5 photos
                        </span>
                    </div>

                    <form
                        class="studio-form"
                        action="{{ route('admin.site-studio.section', $section->id) }}"
                        method="POST"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        @method('PATCH')

                        <div class="studio-field">
                            <label for="section-title-{{ $section->id }}">
                                Section title
                            </label>

                            <input
                                id="section-title-{{ $section->id }}"
                                type="text"
                                name="title"
                                maxlength="120"
                                value="{{ old('title', $section->title) }}"
                                required
                            >
                        </div>

                        <div class="studio-field">
                            <label for="section-description-{{ $section->id }}">
                                Sport description
                            </label>

                            <textarea
                                id="section-description-{{ $section->id }}"
                                name="description"
                                maxlength="1600"
                            >{{ $section->description }}</textarea>
                        </div>

                        <div class="studio-field">
                            <label for="section-order-{{ $section->id }}">
                                Display order
                            </label>

                            <input
                                id="section-order-{{ $section->id }}"
                                type="number"
                                name="sort_order"
                                min="0"
                                max="9999"
                                value="{{ $section->sort_order }}"
                                required
                            >
                        </div>

                        <div class="studio-field">
                            <label for="section-images-{{ $section->id }}">
                                Add sport photos
                            </label>

                            <input
                                id="section-images-{{ $section->id }}"
                                type="file"
                                name="images[]"
                                accept="image/jpeg,image/png,image/webp"
                                multiple
                            >

                            <p class="studio-hint">
                                JPG, PNG or WEBP, up to 4 MB per photo and 5 photos per sport.
                            </p>
                        </div>

                        <button
                            type="submit"
                            class="admin-button"
                        >
                            Save section for {{ $section->sport->name }}
                        </button>
                    </form>

                    {{-- Uploaded Images --}}

                    @if (count($section->images ?? []) > 0)

                        <div class="studio-photo-grid">

                            @foreach ($section->images as $index => $path)

                                <div class="studio-photo">

                                    <img
                                        src="{{ '/storage/' . ltrim($path, '/') }}"
                                        alt="Photo of {{ $section->sport->name }}"
                                        loading="lazy"
                                    >

                                    <form
                                        action="{{ route('admin.site-studio.image.delete', $section->id) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <input
                                            type="hidden"
                                            name="index"
                                            value="{{ $index }}"
                                        >

                                        <button
                                            type="submit"
                                            aria-label="Delete photo for {{ $section->sport->name }}"
                                        >
                                            Delete photo
                                        </button>
                                    </form>

                                </div>

                            @endforeach

                        </div>

                    @endif

                </article>

            @empty

                <div class="studio-empty">
                    No sports are linked to approved courts yet.
                </div>

            @endforelse

        </section>

    @endif

</div>

{{-- =========================================
     Google Maps Confirmation
========================================= --}}

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const forms = document.querySelectorAll('.studio-form');

        forms.forEach((form) => {
            const mapsInput = form.querySelector('[data-studio-maps]');
            const confirmInput = form.querySelector('[data-studio-confirm]');
            const errorMessage = form.querySelector('[data-studio-map-error]');

            if (!mapsInput || !confirmInput) {
                return;
            }

            const hasMapsLink = () => mapsInput.value.trim() !== '';

            const clearError = () => {
                confirmInput.closest('.studio-check')
                    ?.classList.remove('is-invalid');

                errorMessage?.classList.remove('is-visible');
            };

            const showError = () => {
                confirmInput.closest('.studio-check')
                    ?.classList.add('is-invalid');

                errorMessage?.classList.add('is-visible');
            };

            const updateConfirmation = () => {
                confirmInput.required = hasMapsLink();

                if (!hasMapsLink()) {
                    confirmInput.checked = false;
                    clearError();
                }
            };

            mapsInput.addEventListener('input', () => {
                confirmInput.checked = false;
                clearError();
                updateConfirmation();
            });

            confirmInput.addEventListener('change', () => {
                if (confirmInput.checked) {
                    clearError();
                }
            });

            form.addEventListener('submit', (event) => {
                updateConfirmation();

                if (hasMapsLink() && !confirmInput.checked) {
                    event.preventDefault();
                    showError();

                    confirmInput.focus();
                }
            });

            updateConfirmation();
        });
    });
</script>

@endsection
