@extends('admins.layout.app')

@section('title', 'Court Details')

@section('content')
    <x-super-admin.page-header :title="$court->name" description="Court details and listing information.">
        <x-slot:actions>
            <a href="{{ route('admin.courts.index') }}" class="admin-button admin-button-outline">Back to my courts</a>
            <a href="{{ route('admin.courts.edit', $court) }}" class="admin-button">Edit court</a>
        </x-slot:actions>
    </x-super-admin.page-header>

    <div class="super-admin-records">
        <x-dashboard-court-details :court="$court" :photo="$courtPhoto" :owner-url="route('admin.profile.index')" />
    </div>
@endsection
