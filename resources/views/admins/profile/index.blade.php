@extends('admins.layout.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
    @php
        $initial = \Illuminate\Support\Str::of($admin->name)->trim()->substr(0, 1)->upper();
    @endphp

    <div class="super-profile-page">
        <header class="super-profile-hero">
            <div class="super-profile-hero-copy">
                <span class="super-profile-kicker"><i></i>GANG · OWNER ACCOUNT</span>
                <h2>Your profile, your control.</h2>
                <p>Keep your venue account details current so you are ready to manage your courts and bookings.</p>
                <span class="super-profile-role"><i></i>Court Owner</span>
            </div>
            <div class="super-profile-id-card">
                <span class="super-profile-card-label">GANG <small>OWNER</small></span>
                <span class="super-profile-avatar">{{ $initial }}</span>
                <strong>{{ $admin->name }}</strong>
                <small>Venue administrator</small>
                <span class="super-profile-card-orbit" aria-hidden="true"></span>
            </div>
        </header>

        @if ($errors->any())
            <div class="super-profile-alert" role="alert">
                <strong>Please review the information below.</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="super-profile-grid">
            <main class="super-profile-main">
                <section class="super-profile-card" aria-labelledby="owner-profile-details-title">
                    <div class="super-profile-section-head">
                        <span class="super-profile-number">01</span>
                        <div><span>OWNER ACCOUNT</span><h3 id="owner-profile-details-title">Personal information</h3></div>
                    </div>
                    @if (session('success'))
                        <p class="super-profile-success" role="status">{{ session('success') }}</p>
                    @endif
                    <form method="POST" action="{{ route('admin.profile.update') }}" class="super-profile-form">
                        @csrf
                        @method('PUT')
                        <div class="super-profile-field">
                            <label for="name">Full name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $admin->name) }}" autocomplete="name" required>
                            @error('name')<small>{{ $message }}</small>@enderror
                        </div>
                        <div class="super-profile-field">
                            <label for="email">Email address</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $admin->email) }}" autocomplete="email" required>
                            @error('email')<small>{{ $message }}</small>@enderror
                        </div>
                        <div class="super-profile-field">
                            <label for="phone">Phone number <span>Optional</span></label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone', $admin->phone) }}" autocomplete="tel" placeholder="Enter your phone number">
                            @error('phone')<small>{{ $message }}</small>@enderror
                        </div>
                        <div class="super-profile-form-footer">
                            <p>Your details help us keep you updated about your courts and bookings.</p>
                            <button type="submit">Save changes <span aria-hidden="true">→</span></button>
                        </div>
                    </form>
                </section>
            </main>

            <aside class="super-profile-aside">
                <section class="super-profile-tip">
                    <span class="super-profile-tip-icon" aria-hidden="true">✦</span>
                    <span>OWNER TIP</span>
                    <h3>Keep your venue details current.</h3>
                    <p>Players rely on accurate owner contact details when they book and visit your courts.</p>
                </section>
                <div class="super-profile-account-chip">
                    <span aria-hidden="true">✓</span>
                    <div><strong>Owner access</strong><small>Update your account details without changing your court listings or booking records.</small></div>
                </div>
            </aside>
        </div>
    </div>
@endsection
