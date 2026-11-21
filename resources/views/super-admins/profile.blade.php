@extends('layouts.super-admin')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
    <div class="super-profile-page">
        <x-super-admin.page-header title="My Profile" description="Update your administrator details and password." />
        <x-super-admin.form-errors />

        <div class="super-profile-grid">
            <div class="super-profile-main">
                <section class="super-profile-card" aria-labelledby="super-profile-details-title">
                    <div class="super-profile-section-head">
                        <h3 id="super-profile-details-title">Personal information</h3>
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
                            <button class="admin-button" type="submit">Save changes</button>
                        </div>
                    </form>
                </section>

                <section class="super-profile-card" aria-labelledby="super-profile-security-title">
                    <div class="super-profile-section-head">
                        <h3 id="super-profile-security-title">Change password</h3>
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
                            <button class="admin-button" type="submit">Update password</button>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="super-profile-aside">
                <section class="super-profile-tip">
                    <h3>Account security</h3>
                    <p>Use a unique password and update it if you think someone else may know it.</p>
                </section>
            </aside>
        </div>
    </div>
@endsection
