@extends('layouts.super-admin')

@section('title', 'Dashboard')
@section('page-title', 'Platform Overview')

@section('content')
    @php
        $stats = [
            ['label' => 'Players', 'value' => $totalPlayers, 'icon' => 'players', 'tone' => 'green'],
            ['label' => 'Court owners', 'value' => $totalOwners, 'icon' => 'owners', 'tone' => 'blue'],
            ['label' => 'Pending owners', 'value' => $pendingOwners, 'icon' => 'pending', 'tone' => 'amber'],
            ['label' => 'Approved courts', 'value' => $activeCourts, 'icon' => 'courts', 'tone' => 'violet'],
            ['label' => 'Public games', 'value' => $publicGames, 'icon' => 'games', 'tone' => 'rose'],
            ['label' => 'Bookings', 'value' => $totalBookings, 'icon' => 'bookings', 'tone' => 'teal'],
        ];
        $chartStats = [
            ['label' => 'Players', 'value' => $totalPlayers, 'color' => 'green'],
            ['label' => 'Court owners', 'value' => $totalOwners, 'color' => 'blue'],
            ['label' => 'Approved courts', 'value' => $activeCourts, 'color' => 'violet'],
            ['label' => 'Bookings', 'value' => $totalBookings, 'color' => 'teal'],
            ['label' => 'Public games', 'value' => $publicGames, 'color' => 'rose'],
            ['label' => 'Sports', 'value' => $totalSports ?? 0, 'color' => 'amber'],
        ];
    @endphp

    <div class="overview-page">
        <section class="overview-welcome">
            <div class="overview-welcome-copy">
                <span class="overview-eyebrow"><i></i>GANG CONTROL CENTER</span>
                <h2>Good to see you, {{ auth('super_admin')->user()?->name ?? 'Super Admin' }}.</h2>
                <p>Here’s what’s happening across your sports community.</p>
            </div>
            <div class="overview-welcome-side">
                <span class="overview-live"><i></i>Platform snapshot</span>
                <time id="overview-current-date">{{ now()->format('D, M j, Y') }}</time>
            </div>
            <span class="overview-orbit overview-orbit-one" aria-hidden="true"></span>
            <span class="overview-orbit overview-orbit-two" aria-hidden="true"></span>
            <span class="overview-ball overview-ball-one" aria-hidden="true">⚽</span>
            <span class="overview-ball overview-ball-two" aria-hidden="true">🏀</span>
        </section>

        <section class="overview-stats-grid" aria-label="Platform totals">
            @foreach ($stats as $stat)
                <article class="overview-stat-card overview-tone-{{ $stat['tone'] }}">
                    <span class="overview-stat-icon" aria-hidden="true">
                        @switch($stat['icon'])
                            @case('players')
                                <svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="8" r="3.3" stroke="currentColor" stroke-width="1.7"/><path d="M2.8 19c.4-3.1 2.5-4.8 6.2-4.8 1.3 0 2.4.2 3.3.7M16 5.2a3.2 3.2 0 0 1 0 6.2M15 14.5c3.5 0 5.6 1.5 6.1 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                @break
                            @case('owners')
                                <svg viewBox="0 0 24 24" fill="none"><path d="M3 20h18M5 20V9l7-5 7 5v11M9 20v-6h6v6M8 10h.01M16 10h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                @break
                            @case('pending')
                                <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3.2 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                @break
                            @case('courts')
                                <svg viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M12 4v16M3 12h18M8 12a4 4 0 0 1 4-4m0 8a4 4 0 0 0 4-4" stroke="currentColor" stroke-width="1.5"/></svg>
                                @break
                            @case('games')
                                <svg viewBox="0 0 24 24" fill="none"><path d="M7.5 8h9a4.5 4.5 0 0 1 4.3 5.8l-1.1 3.5a2.6 2.6 0 0 1-4.2 1.2L13 16h-2l-2.5 2.5a2.6 2.6 0 0 1-4.2-1.2l-1.1-3.5A4.5 4.5 0 0 1 7.5 8Z" stroke="currentColor" stroke-width="1.7"/><path d="M7 12h4m-2-2v4m7-2h.01m2-2h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                @break
                            @case('bookings')
                                <svg viewBox="0 0 24 24" fill="none"><rect x="3.5" y="5" width="17" height="15.5" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M7.5 3.5v3M16.5 3.5v3M3.5 9.5h17M8 13h2m4 0h2m-8 3.5h2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                @break
                        @endswitch
                    </span>
                    <span class="overview-stat-label">{{ $stat['label'] }}</span>
                    <strong data-count="{{ $stat['value'] }}">{{ number_format($stat['value']) }}</strong>
                    <span class="overview-stat-foot">Current total <i aria-hidden="true">↗</i></span>
                </article>
            @endforeach
        </section>

        <div class="overview-main-grid">
            <section class="overview-panel overview-chart-panel" aria-labelledby="platform-chart-title">
                <div class="overview-panel-head">
                    <div>
                        <span class="overview-section-kicker">PLATFORM AT A GLANCE</span>
                        <h3 id="platform-chart-title">Community overview</h3>
                        <p>Compare current totals across the platform.</p>
                    </div>
                    <span class="overview-chart-legend"><i></i>Current total</span>
                </div>

                <div class="overview-bar-chart" role="img" aria-label="Current totals for platform categories">
                    @foreach ($chartStats as $item)
                        <div class="overview-bar-row" data-chart-value="{{ $item['value'] }}" tabindex="0" aria-label="{{ $item['label'] }}: {{ number_format($item['value']) }}">
                            <span class="overview-bar-label">{{ $item['label'] }}</span>
                            <span class="overview-bar-track"><i class="overview-bar-fill overview-bar-{{ $item['color'] }}"></i></span>
                            <strong class="overview-bar-value">{{ number_format($item['value']) }}</strong>
                        </div>
                    @endforeach
                </div>
                <div class="overview-chart-foot"><span>Counts are shown as they are now.</span><span>Updated on page load</span></div>
            </section>

            <aside class="overview-panel overview-action-panel">
                <div class="overview-panel-head overview-action-head">
                    <div><span class="overview-section-kicker">NEEDS YOUR ATTENTION</span><h3>Owner applications</h3></div>
                    <span class="overview-pending-count">{{ number_format($pendingOwners) }}</span>
                </div>
                <p>Applications waiting for review.</p>
                <a class="overview-action-link" href="{{ route('super_admin.applications.index') }}">
                    Review applications <span aria-hidden="true">→</span>
                </a>
                <div class="overview-action-divider"></div>
                <a class="overview-quick-link" href="{{ route('super_admin.courts.index') }}"><span class="overview-quick-icon">⌂</span><span><strong>Manage courts</strong><small>Review facilities and listings</small></span><b aria-hidden="true">↗</b></a>
                <a class="overview-quick-link" href="{{ route('super_admin.users.index') }}"><span class="overview-quick-icon overview-quick-icon-blue">♙</span><span><strong>Manage players</strong><small>View player accounts</small></span><b aria-hidden="true">↗</b></a>
                <a class="overview-quick-link" href="{{ route('super_admin.admins.index') }}"><span class="overview-quick-icon overview-quick-icon-purple">♧</span><span><strong>Manage owners</strong><small>View court owner accounts</small></span><b aria-hidden="true">↗</b></a>
            </aside>
        </div>

        <div class="overview-tables-grid">
            <section class="overview-panel overview-table-panel">
                <div class="overview-panel-head">
                    <div><span class="overview-section-kicker">LATEST ACCOUNTS</span><h3>Recent owners</h3></div>
                    <a class="overview-view-all" href="{{ route('super_admin.admins.index') }}">View all <span aria-hidden="true">→</span></a>
                </div>
                <div class="overview-table-wrap">
                    <table class="overview-table">
                        <thead><tr><th>Owner</th><th>Business</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse (($recentOwners ?? collect()) as $owner)
                                <tr>
                                    <td><span class="overview-owner-cell"><i>{{ mb_strtoupper(mb_substr($owner->name, 0, 1)) }}</i><span><strong>{{ $owner->name }}</strong><small>{{ $owner->email }}</small></span></span></td>
                                    <td>{{ $owner->business_name ?? '—' }}</td>
                                    <td><span class="overview-status overview-status-{{ $owner->status }}"><i></i>{{ ucfirst($owner->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="overview-table-empty">No owners registered yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overview-panel overview-table-panel">
                <div class="overview-panel-head">
                    <div><span class="overview-section-kicker">FACILITY DIRECTORY</span><h3>Recent courts</h3></div>
                    <a class="overview-view-all" href="{{ route('super_admin.courts.index') }}">View all <span aria-hidden="true">→</span></a>
                </div>
                <div class="overview-table-wrap">
                    <table class="overview-table">
                        <thead><tr><th>Court</th><th>Sport</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse (($recentCourts ?? collect()) as $court)
                                <tr>
                                    <td><span class="overview-court-cell"><i aria-hidden="true">⌂</i><span><strong>{{ $court->name }}</strong><small>{{ $court->admin?->name ?? 'No owner assigned' }}</small></span></span></td>
                                    <td>{{ $court->sports?->name ?? '—' }}</td>
                                    <td><span class="overview-status overview-status-{{ $court->status }}"><i></i>{{ ucfirst($court->status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="overview-table-empty">No courts found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <script>
        (() => {
            const bars = [...document.querySelectorAll('.overview-bar-row')];
            const max = Math.max(1, ...bars.map((bar) => Number(bar.dataset.chartValue) || 0));

            requestAnimationFrame(() => {
                bars.forEach((bar) => {
                    const value = Number(bar.dataset.chartValue) || 0;
                    bar.querySelector('.overview-bar-fill').style.width = `${value === 0 ? 0 : Math.max(3, value / max * 100)}%`;
                });
            });

            document.querySelectorAll('.overview-stat-card strong[data-count]').forEach((node) => {
                const target = Number(node.dataset.count) || 0;
                if (target === 0 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

                const started = performance.now();
                const duration = 650;
                const tick = (now) => {
                    const progress = Math.min(1, (now - started) / duration);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    node.textContent = Math.round(target * eased).toLocaleString();
                    if (progress < 1) requestAnimationFrame(tick);
                };
                requestAnimationFrame(tick);
            });
        })();
    </script>
@endsection
