@extends('admins.layout.app')

@section('title', __('Court Details'))

@section('content')
    <x-super-admin.page-header :title="$court->name" description="{{ __('Court details and listing information.') }}">
        <x-slot:actions>
            <a href="{{ route('admin.courts.index') }}" class="admin-button admin-button-outline">{{ __('Back to my courts') }}</a>
            <a href="{{ route('admin.courts.edit', $court) }}" class="admin-button">{{ __('Edit court') }}</a>
        </x-slot:actions>
    </x-super-admin.page-header>

    <div class="super-admin-records">
        <x-dashboard-court-details :court="$court" :photo="$courtPhoto" :owner-url="route('admin.profile.index')" />
    </div>
@endsection
