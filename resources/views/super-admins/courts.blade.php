@extends('layouts.super-admin')

@section('title', 'Manage Courts')
@section('page-title', 'Courts Management')

@section('content')

<div class="page-header">
    <div>
        <h2>Manage Courts</h2>
        <p>Review, approve, and monitor courts across CourtBook.</p>
    </div>

    <a href="{{ route('super_admin.dashboard') }}"
        class="admin-button admin-button-outline">
        Back to Dashboard
    </a>
</div>

@if ($errors->any())
<div class="admin-card"
    role="alert"
    style="margin-bottom:20px;color:#dc2626">
    @foreach ($errors->all() as $error)
    <p>{{ $error }}</p>
    @endforeach
</div>
@endif

{{-- Search and Filters --}}
<div class="admin-card" style="margin-bottom:24px">

    <div class="admin-card-header">
        <h3>Search & Filter Courts</h3>
    </div>

    <form method="GET"
        action="{{ route('super_admin.courts.index') }}"
        style="display:flex;flex-wrap:wrap;gap:12px;align-items:end">

        <div style="flex:2;min-width:200px">
            <label class="form-label" for="search">
                Search
            </label>

            <input
                id="search"
                class="form-control"
                type="text"
                name="search"
                placeholder="Court, owner or business name"
                value="{{ request('search') }}">
        </div>

        <div style="flex:1;min-width:150px">
            <label class="form-label" for="status">
                Status
            </label>

            <select id="status"
                name="status"
                class="form-control">

                <option value="">All Statuses</option>

                @foreach (['pending', 'approved', 'rejected', 'inactive'] as $status)
                <option value="{{ $status }}"
                    @selected(request('status')===$status)>
                    {{ ucfirst($status) }}
                </option>
                @endforeach

            </select>
        </div>

        <div style="flex:1;min-width:150px">
            <label class="form-label" for="sport_id">
                Sport
            </label>

            <select id="sport_id"
                name="sport_id"
                class="form-control">

                <option value="">All Sports</option>

                @foreach ($sports as $sport)
                <option value="{{ $sport->id }}"
                    @selected(request('sport_id')==$sport->id)>
                    {{ $sport->name }}
                </option>
                @endforeach

            </select>
        </div>

        <button type="submit" class="admin-button">
            Search
        </button>

        <a href="{{ route('super_admin.courts.index') }}"
            class="admin-button admin-button-outline">
            Clear
        </a>
    </form>
</div>

{{-- Courts Table --}}
<div class="admin-card">

    <div class="admin-card-header">
        <h3>Courts ({{ $courts->total() }})</h3>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Court</th>
                    <th>Owner</th>
                    <th>Sport</th>
                    <th>Location</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($courts as $court)
                <tr>
                    <td>{{ $court->id }}</td>

                    <td>
                        <strong>{{ $court->name }}</strong>
                    </td>

                    <td>
                        {{ $court->admin?->name ?? '—' }}
                        <div style="font-size:12px;opacity:.7">
                            {{ $court->admin?->business_name ?? '' }}
                        </div>
                    </td>

                    <td>{{ $court->sports?->name ?? '—' }}</td>

                    <td>{{ $court->address }}</td>

                    <td>
                        @if ($court->reviews_count > 0)
                        ★ {{ number_format($court->reviews_avg_rating, 1) }}
                        <small>({{ $court->reviews_count }})</small>
                        @else
                        No reviews
                        @endif
                    </td>

                    <td>
                        @php
                        $badgeClass = match ($court->status) {
                        'approved' => 'badge-success',
                        'pending' => 'badge-warning',
                        'rejected', 'inactive' => 'badge-danger',
                        default => '',
                        };
                        @endphp

                        <span class="badge {{ $badgeClass }}">
                            {{ ucfirst($court->status) }}
                        </span>
                    </td>

                    <td>
                        <div style="display:flex;flex-direction:column;gap:8px;min-width:140px">

                            <a class="admin-button admin-button-outline"
                                href="{{ route('super_admin.courts.show', $court) }}">
                                View Details
                            </a>

                            <form method="POST"
                                action="{{ route('super_admin.courts.status', $court) }}">
                                @csrf
                                @method('PATCH')

                                <select name="status"
                                    class="form-control"
                                    aria-label="Court status"
                                    style="margin-bottom:6px">

                                    @foreach (['pending', 'approved', 'rejected', 'inactive'] as $status)
                                    <option value="{{ $status }}"
                                        @selected($court->status === $status)>
                                        {{ ucfirst($status) }}
                                    </option>
                                    @endforeach
                                </select>

                                <button class="admin-button"
                                    type="submit">
                                    Save Status
                                </button>
                            </form>

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8"
                        style="text-align:center;padding:32px">
                        No courts found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Simple Pagination --}}
@if ($courts->hasPages())
<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:20px">

    <span>
        Showing {{ $courts->firstItem() }}–{{ $courts->lastItem() }}
        of {{ $courts->total() }}
    </span>

    <div style="display:flex;gap:10px">
        @if ($courts->onFirstPage())
        <span class="admin-button admin-button-outline"
            style="opacity:.5">Previous</span>
        @else
        <a class="admin-button admin-button-outline"
            href="{{ $courts->previousPageUrl() }}">
            Previous
        </a>
        @endif

        @if ($courts->hasMorePages())
        <a class="admin-button"
            href="{{ $courts->nextPageUrl() }}">
            Next
        </a>
        @else
        <span class="admin-button admin-button-outline"
            style="opacity:.5">Next</span>
        @endif
    </div>
</div>
@endif

@endsection