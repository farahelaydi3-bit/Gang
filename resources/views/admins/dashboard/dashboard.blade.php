@extends('admins.layout.app')

@section('title', __('Overview'))
@section('page-title', __('Platform Overview'))
@section('content')
    @php
        $stats = [
            ['label' => __('Total courts'), 'value' => $TotalCourts, 'icon' => 'courts', 'tone' => 'violet'],
            ['label' => __('Reservations'), 'value' => $TotalReservations, 'icon' => 'bookings', 'tone' => 'blue'],
            ['label' => __('Sports'), 'value' => $TotalSports, 'icon' => 'sports', 'tone' => 'amber'],
            ['label' => __('Total revenue'), 'value' => number_format($TotalRevenue, 2) . ' ' . __('JOD'), 'icon' => 'revenue', 'tone' => 'rose'],
        ];
        $chartStats = [
            ['label' => __('Courts'), 'value' => (int) $TotalCourts, 'color' => 'green'],
            ['label' => __('Reservations'), 'value' => (int) $TotalReservations, 'color' => 'blue'],
            ['label' => __('Sports'), 'value' => (int) $TotalSports, 'color' => 'amber'],
        ];
        $chartMax = max(1, ...array_column($chartStats, 'value'));
    @endphp

    <div class="overview-page">
        <x-super-admin.page-header title="{{ __('Platform Overview') }}" :description="__('Manage courts and reservations for :owner.', ['owner' => auth('admin')->user()->business_name ?: auth('admin')->user()->name])">
            <x-slot:actions>
                <div class="super-admin-page-meta"><time datetime="{{ now()->toDateString() }}">{{ now()->locale(app()->getLocale())->translatedFormat('D, M j, Y') }}</time></div>
            </x-slot:actions>
        </x-super-admin.page-header>

        <section class="overview-stats-grid owner-overview-stats" aria-label="{{ __('Owner dashboard totals') }}">
            @foreach ($stats as $stat)
                <article class="overview-stat-card overview-tone-{{ $stat['tone'] }}">
                    <span class="overview-stat-icon" aria-hidden="true">
                        @if ($stat['icon'] === 'courts')
                            <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M12 4v16M3 12h18M8 12a4 4 0 0 1 4-4m0 8a4 4 0 0 0 4-4" stroke="currentColor" stroke-width="1.5"/></svg>
                        @elseif ($stat['icon'] === 'bookings')
                            <svg viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="15.5" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M7.5 3.5v3M16.5 3.5v3M3.5 9.5h17M8 13h2m4 0h2m-8 3.5h2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                        @elseif ($stat['icon'] === 'sports')
                            <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="m8 5 4 3 4-3M6 12l4 2-1 5M18 12l-4 2 1 5" stroke="currentColor" stroke-width="1.5"/></svg>
                        @else
                            <svg viewBox="0 0 24 24" fill="none"><path d="M12 3v18m5-14c0-1.7-2-3-5-3s-5 1.3-5 3 2 3 5 3 5 1.3 5 3-2 3-5 3-5-1.3-5-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        @endif
                    </span>
                    <span class="overview-stat-label">{{ $stat['label'] }}</span>
                    <strong>{{ is_numeric($stat['value']) ? number_format($stat['value']) : $stat['value'] }}</strong>
                    <span class="overview-stat-foot">{{ __('Current total') }} <i aria-hidden="true">↗</i></span>
                </article>
            @endforeach
        </section>

        <div class="overview-main-grid">
            <section class="overview-panel overview-chart-panel" aria-labelledby="owner-chart-title">
                <div class="overview-panel-head">
                    <div>
                        <span class="overview-section-kicker">{{ __('YOUR FACILITIES AT A GLANCE') }}</span>
                        <h3 id="owner-chart-title">{{ __('Owner overview') }}</h3>
                        <p>{{ __('Compare your courts, reservations, and sports.') }}</p>
                    </div>
                    <span class="overview-chart-legend"><i></i>{{ __('Current total') }}</span>
                </div>
                <div class="overview-bar-chart" role="img" aria-label="{{ __('Current totals for owner facilities') }}">
                    @foreach ($chartStats as $item)
                        <div class="overview-bar-row" tabindex="0" aria-label="{{ $item['label'] }}: {{ number_format($item['value']) }}">
                            <span class="overview-bar-label">{{ $item['label'] }}</span>
                            <span class="overview-bar-track"><i class="overview-bar-fill overview-bar-{{ $item['color'] }}" style="width:{{ $item['value'] > 0 ? max(4, ($item['value'] / $chartMax) * 100) : 0 }}%"></i></span>
                            <strong class="overview-bar-value">{{ number_format($item['value']) }}</strong>
                        </div>
                    @endforeach
                </div>
                <div class="overview-chart-foot"><span>{{ __('Counts reflect your account.') }}</span><span>{{ __('Updated on page load') }}</span></div>
            </section>

            <aside class="overview-panel overview-action-panel owner-overview-action">
                <div class="overview-panel-head overview-action-head">
                    <div><span class="overview-section-kicker">{{ __('QUICK ACTIONS') }}</span><h3>{{ __('Manage your venue') }}</h3></div>
                </div>
                <p>{{ __('Keep your facilities and bookings up to date.') }}</p>
                <a class="overview-action-link" href="{{ route('admin.courts.create') }}">{{ __('Add a court') }} <span aria-hidden="true">→</span></a>
                <div class="overview-action-divider"></div>
                <a class="overview-quick-link" href="{{ route('admin.courts.index') }}"><span class="overview-quick-icon">⌂</span><span><strong>{{ __('My courts') }}</strong><small>{{ __('Manage your facility listings') }}</small></span><b aria-hidden="true">↗</b></a>
                <a class="overview-quick-link" href="{{ route('admin.reservations.index') }}"><span class="overview-quick-icon overview-quick-icon-blue">▦</span><span><strong>{{ __('Reservations') }}</strong><small>{{ __('Review upcoming bookings') }}</small></span><b aria-hidden="true">↗</b></a>
            </aside>
        </div>

        <section class="overview-panel overview-table-panel">
            <div class="overview-panel-head">
                <div><h3>{{ __('My courts') }}</h3></div>
                <a class="overview-view-all" href="{{ route('admin.courts.index') }}">{{ __('View all') }} <span aria-hidden="true">→</span></a>
            </div>

<div class="overview-table-wrap">
    <table class="overview-table admin-table">
        <thead>
            <tr>
                <th scope="col">{{ __('Image') }}</th>
                <th scope="col">{{ __('Court') }}</th>
                <th scope="col">{{ __('Sport') }}</th>
                <th scope="col">{{ __('Address') }}</th>
                <th scope="col">{{ __('Status') }}</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($MyCourts as $court)
                @php
                    $imageUrl = $court->thumbnailUrl();
                @endphp

                <tr>
                    <td>
                        <img
                            src="{{ $imageUrl }}"
                            alt="{{ $court->name }}"
                            style="width: 85px; height: 60px; object-fit: cover; border-radius: 8px;">
                    </td>

                    <td>
                        <a class="super-admin-record-link"
                           href="{{ route('admin.courts.show', $court) }}"
                           dir="auto">
                            {{ $court->name }}
                        </a>
                    </td>

                    <td>{{ __($court->sports?->name ?? '—') }}</td>

                    <td dir="auto">{{ $court->address }}</td>

                    <td>
                        <x-dashboard-status :state="$court->status" />
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="5" class="overview-table-empty">
                        {{ __('You haven’t added any courts yet.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

            </div>
        </section>

        <section class="overview-panel overview-table-panel">
            <div class="overview-panel-head">
                <div><span class="overview-section-kicker">{{ __('LATEST ACTIVITY') }}</span><h3>{{ __('Recent reservations') }}</h3></div>
                <a class="overview-view-all" href="{{ route('admin.reservations.index') }}">{{ __('View all') }} <span aria-hidden="true">→</span></a>
            </div>
            <div class="overview-table-wrap">
                <table class="overview-table admin-table">
                    <thead><tr><th>{{ __('Customer') }}</th><th>{{ __('Court') }}</th><th>{{ __('Date') }}</th><th>{{ __('Time') }}</th><th>{{ __('Status') }}</th></tr></thead>
                    <tbody>
                        @forelse ($RecentReservations as $reservation)
                            <tr>
                                <td>{{ $reservation->user->name ?? 'Unknown User' }}</td>
                                <td>
                                    @if ($reservation->court)
                                        <a class="super-admin-record-link" href="{{ route('admin.courts.show', $reservation->court) }}">{{ $reservation->court->name }}</a>
                                    @else
                                        {{ __('Unknown court') }}
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($reservation->booking_date)->format('M d, Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($reservation->end_time)->format('h:i A') }}</td>
                                <td><x-dashboard-status :state="$reservation->booking_status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="overview-table-empty">{{ __('No reservations found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
