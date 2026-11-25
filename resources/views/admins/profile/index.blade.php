@extends('admins.layout.app')

@section('title', __('My Profile'))
@section('page-title', __('My Profile'))

@section('content')
    <div class="super-profile-page">
        <x-super-admin.page-header title="{{ __('My Profile') }}" description="{{ __('Update your owner account and contact details.') }}" />
        <x-super-admin.form-errors />

        <div class="super-profile-grid">
            <div class="super-profile-main">
                <section class="super-profile-card" aria-labelledby="owner-profile-details-title">
                    <div class="super-profile-section-head">
                        
                        <h3 id="owner-profile-details-title">{{ __('Personal information') }}</h3>
                    </div>
                    <form method="POST" action="{{ route('admin.profile.update') }}" class="super-profile-form">
                        @csrf
                        @method('PUT')
                        <div class="super-profile-field">
                            <label for="name">{{ __('Full name') }}</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $admin->name) }}" autocomplete="name" required>
                            @error('name')<small>{{ $message }}</small>@enderror
                        </div>
                        <div class="super-profile-field">
                            <label for="email">{{ __('Email address') }}</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $admin->email) }}" autocomplete="email" required>
                            @error('email')<small>{{ $message }}</small>@enderror
                        </div>
                        <div class="super-profile-field">
                            <label for="phone">{{ __('Phone number') }} <span>{{ __('Optional') }}</span></label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone', $admin->phone) }}" autocomplete="tel" placeholder="{{ __('Enter your phone number') }}">
                            @error('phone')<small>{{ $message }}</small>@enderror
                        </div>
                        <div class="super-profile-form-footer">
                            <p>{{ __('Your details help us keep you updated about your courts and bookings.') }}</p>
                            <button type="submit" class="admin-button">{{ __('Save changes') }}</button>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="super-profile-aside">
                <section class="super-profile-tip">
                    <span class="super-profile-tip-icon" aria-hidden="true">✦</span>
                    <span>{{ __('OWNER TIP') }}</span>
                    <h3>{{ __('Keep your venue details current.') }}</h3>
                    <p>{{ __('Players rely on accurate owner contact details when they book and visit your courts.') }}</p>
                </section>
                <div class="super-profile-account-chip">
                    <span aria-hidden="true">✓</span>
                    <div><strong>{{ __('Owner access') }}</strong><small>{{ __('Update your account details without changing your court listings or booking records.') }}</small></div>
                </div>
            </aside>
        </div>
    </div>
@endsection
