@extends('layouts.super-admin')

@section('title', __('Owner Details'))

@section('content')
    <x-super-admin.page-header :title="$admin->name" :description="$admin->business_name" :reference="__('Owner #:number', ['number' => $admin->id])">
        <x-slot:actions>
            <a href="{{ route('super_admin.admins.index') }}" class="admin-button admin-button-outline">
                <span aria-hidden="true">←</span> {{ __('Back to owners') }}
            </a>
        </x-slot:actions>
    </x-super-admin.page-header>

    <div class="super-admin-records">
        <section class="admin-card" aria-labelledby="owner-information">
            <div class="admin-card-header"><h2 id="owner-information">{{ __('Owner information') }}</h2></div>
            @php
                $statusClass = match ($admin->status) {
                    'active' => 'badge-success',
                    'pending' => 'badge-warning',
                    'rejected', 'suspended' => 'badge-danger',
                    default => '',
                };
            @endphp

            <dl class="super-admin-record-facts">
                <div><dt>{{ __('Email address') }}</dt><dd>{{ $admin->email }}</dd></div>
                <div><dt>{{ __('Phone number') }}</dt><dd>{{ $admin->phone ?: '—' }}</dd></div>
                <div><dt>{{ __('Business name') }}</dt><dd dir="auto">{{ $admin->business_name ?: '—' }}</dd></div>
                <div>
                    <dt>{{ __('Account status') }}</dt>
                    <dd><span class="badge {{ $statusClass }}">{{ __(ucfirst($admin->status ?? 'Unknown')) }}</span></dd>
                </div>
                <div>
                    <dt>{{ __('Registered') }}</dt>
                    <dd>
                        @if ($admin->created_at)
                            <time datetime="{{ $admin->created_at->toDateString() }}">{{ $admin->created_at->format('M j, Y') }}</time>
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
        </section>

        <section class="admin-card super-admin-table-card" aria-labelledby="owner-courts">
            <div class="admin-card-header"><h2 id="owner-courts">{{ __('Courts (') }}{{ $courts->total() }})</h2></div>
            <x-super-admin.list-filters :action="route('super_admin.admins.show', $admin)"
                placeholder="Court name or address"
                :selects="['status' => ['Status', 'All statuses', $statuses]]" />
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Court') }}</th>
                            <th scope="col">{{ __('Sport') }}</th>
                            <th scope="col">{{ __('Address') }}</th>
                            <th scope="col">{{ __('Listing status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($courts as $court)
                            @php
                                $statusClass = match ($court->status) {
                                    'approved' => 'badge-success',
                                    'pending' => 'badge-warning',
                                    'inactive', 'rejected' => 'badge-danger',
                                    default => '',
                                };
                            @endphp
                            <tr>
                                <td><a class="super-admin-record-link" href="{{ route('super_admin.courts.show', $court) }}" dir="auto">{{ $court->name }}</a></td>
                                <td>{{ __($court->sports?->name ?? '—') }}</td>
                                <td dir="auto">{{ $court->address ?: '—' }}</td>
                                <td><span class="badge {{ $statusClass }}">{{ __(ucfirst($court->status)) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="super-admin-empty">{{ __(\App\Support\DashboardList::hasFilters(request()) ? 'No results match your filters.' : 'This owner has no courts yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $courts->links('components.super-admin.pagination') }}
        </section>
    </div>
@endsection
