@extends('layouts.super-admin')

@section('title', __('Owner Applications'))

@section('content')
    <x-super-admin.page-header title="{{ __('Owner Applications') }}" description="{{ __('Review applications from sports facility owners.') }}" />
    <x-super-admin.form-errors />

    <section class="admin-card super-admin-filter" aria-label="{{ __('Filter applications') }}">
        <form class="super-admin-filter-form" method="GET" action="{{ route('super_admin.applications.index') }}">
            <div class="super-admin-filter-field">
                <label class="form-label" for="status">{{ __('Status') }}</label>
                <select class="form-control" name="status" id="status">
                    <option value="">{{ __('All statuses') }}</option>
                    <option value="submitted" @selected(request('status') === 'submitted')>{{ __('Under review') }}</option>
                    <option value="approved" @selected(request('status') === 'approved')>{{ __('Approved') }}</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>{{ __('Rejected') }}</option>
                </select>
            </div>
            <button type="submit" class="admin-button">{{ __('Filter') }}</button>
            @if (request()->filled('status'))
                <a href="{{ route('super_admin.applications.index') }}" class="admin-button admin-button-outline">{{ __('Clear') }}</a>
            @endif
        </form>
    </section>

    <section class="admin-card super-admin-table-card" aria-labelledby="owner-applications-results">
        <div class="admin-card-header"><h2 id="owner-applications-results">{{ __('Applications (') }}{{ $applications->total() }})</h2></div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">{{ __('ID') }}</th>
                        <th scope="col">{{ __('Owner') }}</th>
                        <th scope="col">{{ __('Facility') }}</th>
                        <th scope="col">{{ __('Status') }}</th>
                        <th scope="col">{{ __('Submitted') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        @php
                            [$statusLabel, $statusClass] = match ($application->status) {
                                'submitted' => ['Under review', 'badge-warning'],
                                'approved' => ['Approved', 'badge-success'],
                                'rejected' => ['Rejected', 'badge-danger'],
                                default => [ucfirst($application->status), ''],
                            };
                        @endphp
                        <tr>
                            <td>#{{ $application->id }}</td>
                            <td>
                                <strong dir="auto">{{ $application->admin?->name ?? 'Unknown' }}</strong>
                                <div class="super-admin-muted">{{ $application->admin?->email }}</div>
                            </td>
                            <td dir="auto">{{ $application->facility_name }}</td>
                            <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                            <td>{{ $application->submitted_at?->format('M j, Y') ?? '—' }}</td>
                            <td><a href="{{ route('super_admin.applications.show', $application) }}" class="admin-button">{{ __('Review') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="super-admin-empty">{{ __('No applications found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $applications->withQueryString()->links('components.super-admin.pagination') }}
    </section>
@endsection
