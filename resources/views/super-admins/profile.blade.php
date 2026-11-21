@extends('layouts.super-admin')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
    @php
        $initial = \Illuminate\Support\Str::of($superAdmin->name)->trim()->substr(0, 1)->upper();
    @endphp

    <div class="super-profile-page">
        <header class="super-profile-hero">
            <div class="super-profile-hero-copy">
                <span class="super-profile-kicker"><i></i>COURTBOOK · PLATFORM ADMIN</span>
                <h2>Your profile, your control.</h2>
                <p>Keep your administrator details current and your account secure.</p>
                <span class="super-profile-role"><i></i>Super Admin</span>
            </div>
            <div class="super-profile-id-card">
                <span class="super-profile-card-label">COURTBOOK <small>ADMIN</small></span>
                <span class="super-profile-avatar">{{ $initial }}</span>
                <strong>{{ $superAdmin->name }}</strong>
                <small>Platform administrator</small>
                <span class="super-profile-card-orbit" aria-hidden="true"></span>
            </div>
        </header>

        @if ($errors->any())
            <div class="super-profile-alert" role="alert">
                <strong>Please review the highlighted information.</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="super-profile-grid">
            <main class="super-profile-main">
                <section class="super-profile-card" aria-labelledby="super-profile-details-title">
                    <div class="super-profile-section-head">
                        <span class="super-profile-number">01</span>
                        <div><span>ADMIN ACCOUNT</span><h3 id="super-profile-details-title">Personal information</h3></div>
                    </div>
                    @if (session('profile-updated'))
                        <p class="super-profile-success" role="status">Your profile information has been saved.</p>
                    @endif
                    <form method="POST" action="{{ route('super_admin.profile.update') }}" class="super-profile-form">
                        @csrf
                        @method('PATCH')
                        <div class="super-profile-field">
                            <label for="name">Full name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $superAdmin->name) }}" autocomplete="name" required>
                            @error('name')<small>{{ $message }}</small>@enderror
                        </div>
                        <div class="super-profile-field">
                            <label for="email">Email address</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $superAdmin->email) }}" autocomplete="email" required>
                            @error('email')<small>{{ $message }}</small>@enderror
                        </div>
                        <div class="super-profile-field">
                            <label for="phone">Phone number <span>Optional</span></label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone', $superAdmin->phone) }}" autocomplete="tel">
                            @error('phone')<small>{{ $message }}</small>@enderror
                        </div>
                        <div class="super-profile-form-footer">
                            <p>Your information is used to manage the CourtBook platform account.</p>
                            <button type="submit">Save changes <span aria-hidden="true">→</span></button>
                        </div>
                    </form>
                </section>

                <section class="super-profile-card" aria-labelledby="super-profile-security-title">
                    <div class="super-profile-section-head">
                        <span class="super-profile-number">02</span>
                        <div><span>ACCOUNT SECURITY</span><h3 id="super-profile-security-title">Change password</h3></div>
                    </div>
                    @if (session('password-updated'))
                        <p class="super-profile-success" role="status">Your password has been updated.</p>
                    @endif
                    <form method="POST" action="{{ route('super_admin.profile.password') }}" class="super-profile-form">
                        @csrf
                        @method('PUT')
                        <div class="super-profile-field">
                            <label for="current_password">Current password</label>
                            <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                            @error('current_password')<small>{{ $message }}</small>@enderror
                        </div>
                        <div class="super-profile-field">
                            <label for="password">New password</label>
                            <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
                            @error('password')<small>{{ $message }}</small>@enderror
                        </div>
                        <div class="super-profile-field">
                            <label for="password_confirmation">Confirm new password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                        </div>
                        <div class="super-profile-form-footer">
                            <p>Choose a password with at least 8 characters.</p>
                            <button type="submit">Update password <span aria-hidden="true">→</span></button>
                        </div>
                    </form>
                </section>
            </main>

            <aside class="super-profile-aside">
                <section class="super-profile-tip">
                    <span class="super-profile-tip-icon" aria-hidden="true">✦</span>
                    <span>ADMIN TIP</span>
                    <h3>Keep your access protected.</h3>
                    <p>Use a unique password and update it if you think someone else may know it.</p>
                </section>
                <div class="super-profile-account-chip">
                    <span aria-hidden="true">✓</span>
                    <div><strong>Administrator access</strong><small>Manage your details without affecting platform records.</small></div>
                </div>
            </aside>
        </div>
    </div>
@endsection
