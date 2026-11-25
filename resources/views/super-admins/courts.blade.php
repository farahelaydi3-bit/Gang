@extends('layouts.super-admin')

@section('title', __('Manage Courts'))
@section('page-title', 'Courts Management')

@section('content')

<x-super-admin.page-header title="{{ __('Manage Courts') }}" description="{{ __('Find courts and review their listing status.') }}" />

<x-super-admin.form-errors />

<x-super-admin.list-filters :action="route('super_admin.courts.index')"
    placeholder="Court, address, owner or business"
    :selects="['status' => ['Status', 'All statuses', $statuses], 'sport_id' => ['Sport', 'All Sports', $sportOptions]]" />

{{-- Courts Table --}}
<div class="admin-card super-admin-table-card">

    <div class="admin-card-header">
        <h3>{{ __('Courts (') }}{{ $courts->total() }})</h3>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">

            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Court') }}</th>
                    <th>{{ __('Owner') }}</th>
                    <th>{{ __('Sport') }}</th>
                    <th>{{ __('Location') }}</th>
                    <th>{{ __('Rating') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($courts as $court)
                <tr>
                    <td>{{ $court->id }}</td>

                    <td>
                        <a class="super-admin-record-link" href="{{ route('super_admin.courts.show', $court) }}" dir="auto">
                            {{ $court->name }}
                        </a>
                    </td>

                    <td>
                        @if ($court->admin)
                            <a class="super-admin-record-link" href="{{ route('super_admin.admins.show', $court->admin) }}" dir="auto">
                                {{ $court->admin->name }}
                            </a>
                            <div class="super-admin-muted" dir="auto">{{ $court->admin->business_name }}</div>
                        @else
                            —
                        @endif
                    </td>

                    <td>{{ __($court->sports?->name ?? '—') }}</td>

                    <td>{{ $court->address }}</td>

                    <td>
                        @if ($court->reviews_count > 0)
                        ★ {{ number_format($court->reviews_avg_rating, 1) }}
                        <small>({{ $court->reviews_count }})</small>
                        @else
                        {{ __('No reviews') }}
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
                            {{ __(ucfirst($court->status)) }}
                        </span>
                    </td>

                    <td>
                        <div class="super-admin-records">

                            <a class="admin-button admin-button-outline"
                                href="{{ route('super_admin.courts.show', $court) }}">
                                {{ __('View Details') }}
                            </a>

                            <form method="POST"
                                action="{{ route('super_admin.courts.status', $court) }}" class="super-admin-inline-actions">
                                @csrf
                                @method('PATCH')

                                <select name="status"
                                    class="form-control"
                                    aria-label="{{ __('Court status') }}">

                                    @foreach (['pending', 'approved', 'rejected', 'inactive'] as $status)
                                    <option value="{{ $status }}"
                                        @selected($court->status === $status)>
                                        {{ __(ucfirst($status)) }}
                                    </option>
                                    @endforeach
                                </select>

                                <button class="admin-button"
                                    type="submit">
                                    {{ __('Save Status') }}
                                </button>
                            </form>

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="super-admin-empty">
                        {{ __(\App\Support\DashboardList::hasFilters(request()) ? 'No results match your filters.' : 'No courts found.') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $courts->withQueryString()->links('components.super-admin.pagination') }}
</div>

@endsection
