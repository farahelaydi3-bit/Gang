@extends('layouts.super-admin')

@section('title', 'Manage Courts')
@section('page-title', 'Courts Management')

@section('content')

<x-super-admin.page-header title="Manage Courts" description="Find courts and review their listing status." />

<x-super-admin.form-errors />

{{-- Search and Filters --}}
<div class="admin-card super-admin-filter">

    <div class="admin-card-header">
        <h3>Search & Filter Courts</h3>
    </div>

    <form method="GET"
        action="{{ route('super_admin.courts.index') }}"
        class="super-admin-filter-form">

        <div class="super-admin-filter-field super-admin-filter-field--search">
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

        <div class="super-admin-filter-field">
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

        <div class="super-admin-filter-field">
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
<div class="admin-card super-admin-table-card">

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
                        <div class="super-admin-records">

                            <a class="admin-button admin-button-outline"
                                href="{{ route('super_admin.courts.show', $court) }}">
                                View Details
                            </a>

                            <form method="POST"
                                action="{{ route('super_admin.courts.status', $court) }}" class="super-admin-inline-actions">
                                @csrf
                                @method('PATCH')

                                <select name="status"
                                    class="form-control"
                                    aria-label="Court status">

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
                    <td colspan="8" class="super-admin-empty">
                        No courts found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $courts->withQueryString()->links('components.super-admin.pagination') }}
</div>

@endsection
