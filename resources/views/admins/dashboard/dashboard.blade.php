@extends('admins.layout.app')

@section('title', 'Overview')
@section('page-title', 'Platform Overview')
@section('page-class', 'owner-overview-content')

@push('styles')
    <style>
        .owner-overview-content{max-width:none!important;padding:26px 30px!important}
        .owner-overview-content>.overview-page{max-width:1450px;margin:0 auto}
        .owner-overview-courts .court-info{padding:16px}
        .owner-overview-courts .court-info h3{color:#1D3557}
        .owner-overview-courts .court-prices{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px 12px}
        .owner-overview-courts .court-prices p{font-size:11px;color:#52657E}
        .owner-overview-courts .court-prices strong{color:#1D3557}
        .owner-overview-courts .court-deposit{font-size:11px;color:#68778B}
        .owner-overview-courts .court-status{background:#F3E9DC;color:#B5651D}
        .owner-overview-empty{grid-column:1/-1}
        .owner-overview-action .overview-action-link{background:#B5651D}
        .owner-overview-action .overview-action-link:hover{background:#965014}
        @media(max-width:900px){.owner-overview-content{padding:68px 22px 22px!important}}
        @media(max-width:560px){.owner-overview-content{padding:68px 14px 14px!important}.owner-overview-courts .court-prices{grid-template-columns:1fr}}
    </style>
@endpush

@section('content')
    @php
        $stats = [
            ['label' => 'Total courts', 'value' => $TotalCourts, 'icon' => 'courts', 'tone' => 'violet'],
            ['label' => 'Reservations', 'value' => $TotalReservations, 'icon' => 'bookings', 'tone' => 'blue'],
            ['label' => 'Sports', 'value' => $TotalSports, 'icon' => 'sports', 'tone' => 'amber'],
            ['label' => 'Total revenue', 'value' => number_format($TotalRevenue, 2) . ' JOD', 'icon' => 'revenue', 'tone' => 'rose'],
        ];
        $chartStats = [
            ['label' => 'Courts', 'value' => (int) $TotalCourts, 'color' => 'green'],
            ['label' => 'Reservations', 'value' => (int) $TotalReservations, 'color' => 'blue'],
            ['label' => 'Sports', 'value' => (int) $TotalSports, 'color' => 'amber'],
        ];
        $chartMax = max(1, ...array_column($chartStats, 'value'));
    @endphp

    <div class="overview-page">
        <section class="overview-welcome">
<div class="overview-welcome-copy">
                <span class="overview-eyebrow"><i></i>GANG OWNER CENTER</span>
                <h2>Welcome back, {{ Auth::guard('admin')->user()->name }}.</h2>
                <p>Here’s what’s happening across your sports facilities.</p>
            </div>
            <div class="overview-welcome-side">
                <span class="overview-live"><i></i>Owner snapshot</span>
                <time>{{ now()->format('D, M j, Y') }}</time>
            </div>
            <span class="overview-orbit overview-orbit-one" aria-hidden="true"></span>
            <span class="overview-orbit overview-orbit-two" aria-hidden="true"></span>
            <span class="overview-ball overview-ball-one" aria-hidden="true">⚽</span>
            <span class="overview-ball overview-ball-two" aria-hidden="true">🏀</span>
        </section>

        <section class="overview-stats-grid" aria-label="Owner dashboard totals">
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
                    <span class="overview-stat-foot">Current total <i aria-hidden="true">↗</i></span>
                </article>
            @endforeach
        </section>

        <div class="overview-main-grid">
            <section class="overview-panel overview-chart-panel" aria-labelledby="owner-chart-title">
                <div class="overview-panel-head">
                    <div>
                        <span class="overview-section-kicker">YOUR FACILITIES AT A GLANCE</span>
                        <h3 id="owner-chart-title">Owner overview</h3>
                        <p>Compare your courts, reservations, and sports.</p>
                    </div>
                    <span class="overview-chart-legend"><i></i>Current total</span>
                </div>
                <div class="overview-bar-chart" role="img" aria-label="Current totals for owner facilities">
                    @foreach ($chartStats as $item)
                        <div class="overview-bar-row" tabindex="0" aria-label="{{ $item['label'] }}: {{ number_format($item['value']) }}">
                            <span class="overview-bar-label">{{ $item['label'] }}</span>
                            <span class="overview-bar-track"><i class="overview-bar-fill overview-bar-{{ $item['color'] }}" style="width:{{ $item['value'] > 0 ? max(4, ($item['value'] / $chartMax) * 100) : 0 }}%"></i></span>
                            <strong class="overview-bar-value">{{ number_format($item['value']) }}</strong>
                        </div>
                    @endforeach
                </div>
                <div class="overview-chart-foot"><span>Counts reflect your account.</span><span>Updated on page load</span></div>
            </section>

            <aside class="overview-panel overview-action-panel owner-overview-action">
                <div class="overview-panel-head overview-action-head">
                    <div><span class="overview-section-kicker">QUICK ACTIONS</span><h3>Manage your venue</h3></div>
                </div>
                <p>Keep your facilities and bookings up to date.</p>
                <a class="overview-action-link" href="{{ route('admin.courts.create') }}">Add a court <span aria-hidden="true">→</span></a>
                <div class="overview-action-divider"></div>
                <a class="overview-quick-link" href="{{ route('admin.courts.index') }}"><span class="overview-quick-icon">⌂</span><span><strong>My courts</strong><small>Manage your facility listings</small></span><b aria-hidden="true">↗</b></a>
                <a class="overview-quick-link" href="{{ route('admin.reservations.index') }}"><span class="overview-quick-icon overview-quick-icon-blue">▦</span><span><strong>Reservations</strong><small>Review upcoming bookings</small></span><b aria-hidden="true">↗</b></a>
            </aside>
        </div>

        <section class="overview-panel overview-table-panel owner-overview-courts">
            <div class="overview-panel-head">
                <div><span class="overview-section-kicker">YOUR FACILITIES</span><h3>My Courts</h3><p>Courts currently connected to your account.</p></div>
                <a class="overview-view-all" href="{{ route('admin.courts.index') }}">View all <span aria-hidden="true">→</span></a>
            </div>
            <div class="courts-grid">
                @forelse ($MyCourts as $court)
                    <article class="court-card">
                        <div class="court-image"><img src="{{ asset('courtbook-assets/images/' . $court->image) }}" alt="{{ $court->name }}"></div>
                        <div class="court-info">
                            <h3>{{ $court->name }}</h3>
                            <p class="court-sport">{{ $court->sports->name ?? 'Unknown Sport' }}</p>
                            <div class="court-prices">
                                <p><strong>Weekday:</strong> {{ number_format($court->weekday_price, 2) }} JOD</p>
                                <p><strong>Weekend:</strong> {{ number_format($court->weekend_price, 2) }} JOD</p>
                                <p><strong>Evening:</strong> {{ number_format($court->evening_price, 2) }} JOD</p>
                            </div>
                            <p class="court-deposit">Deposit: {{ number_format($court->deposit_amount, 2) }} JOD</p>
                            <span class="court-status">{{ ucfirst($court->status) }}</span>
                        </div>
                    </article>
                @empty
                    <div class="empty-state owner-overview-empty"><p>You don't have any courts yet.</p></div>
                @endforelse
            </div>
        </section>

        <section class="overview-panel overview-table-panel">
            <div class="overview-panel-head">
                <div><span class="overview-section-kicker">LATEST ACTIVITY</span><h3>Recent reservations</h3></div>
                <a class="overview-view-all" href="{{ route('admin.reservations.index') }}">View all <span aria-hidden="true">→</span></a>
            </div>
            <div class="table-responsive">
                <table class="overview-table admin-table">
                    <thead><tr><th>Customer</th><th>Court</th><th>Date</th><th>Time</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($RecentReservations as $reservation)
                            <tr>
                                <td>{{ $reservation->user->name ?? 'Unknown User' }}</td>
                                <td>{{ $reservation->court->name ?? 'Unknown Court' }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->booking_date)->format('M d, Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($reservation->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($reservation->end_time)->format('h:i A') }}</td>
                                <td><span class="status-badge status-{{ strtolower($reservation->booking_status) }}">{{ ucfirst($reservation->booking_status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="overview-table-empty">No reservations found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection