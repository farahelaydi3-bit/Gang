@extends('admins.layout.app')

@section('title', 'Overview')
@section('page-title', 'Overview')

@section('content')

    <div class="dashboard">

        {{-- Welcome Section --}}
        <div class="page-header">
            <div>
                <h1>Welcome back, {{ Auth::guard('admin')->user()->name }} 👋</h1>
                <p>Here's what's happening with your courts today.</p>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="stats-grid">

            {{-- Total Courts --}}
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Total Courts</span>
                    <h2>{{ $TotalCourts }}</h2>
                </div>
                <div class="stat-icon">🏟️</div>
            </div>

            {{-- Total Reservations --}}
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Total Reservations</span>
                    <h2>{{ $TotalReservations }}</h2>
                </div>
                <div class="stat-icon">📅</div>
            </div>

            {{-- Total Sports --}}
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Total Sports</span>
                    <h2>{{ $TotalSports }}</h2>
                </div>
                <div class="stat-icon">⚽</div>
            </div>

            {{-- Total Revenue --}}
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Total Revenue</span>
                    <h2>{{ number_format($TotalRevenue, 2) }} JOD</h2>
                </div>
                <div class="stat-icon">💰</div>
            </div>

        </div>


        {{-- My Courts Section --}}
        <div class="section-card">

            <div class="section-header">
                <h2>My Courts</h2>
                <a href="#" class="view-all">View All</a>
            </div>

            <div class="courts-grid">

                @forelse($MyCourts as $court)

                    <div class="court-card">

                        {{-- Court Image --}}
                        <div class="court-image">
                            <img src="{{ asset('courtbook-assets/images/' . $court->image) }}" alt="{{ $court->name }}">
                        </div>

                        {{-- Court Details --}}
                        <div class="court-info">

                            <h3>{{ $court->name }}</h3>

                            <p class="court-sport">
                                {{ $court->sports->name ?? 'Unknown Sport' }}
                            </p>

                            <div class="court-prices">
                                <p>
                                    <strong>Weekday:</strong>
                                    {{ number_format($court->weekday_price, 2) }} JOD
                                </p>

                                <p>
                                    <strong>Weekend:</strong>
                                    {{ number_format($court->weekend_price, 2) }} JOD
                                </p>

                                <p>
                                    <strong>Evening:</strong>
                                    {{ number_format($court->evening_price, 2) }} JOD
                                </p>
                            </div>

                            <p class="court-deposit">
                                Deposit: {{ number_format($court->deposit_amount, 2) }} JOD
                            </p>

                            <span class="court-status">
                                {{ ucfirst($court->status) }}
                            </span>

                        </div>
                    </div>

                @empty

                    <div class="empty-state">
                        <p>You don't have any courts yet.</p>
                    </div>

                @endforelse

            </div>

        </div>


        {{-- Recent Reservations Section --}}
        <div class="section-card">

            <div class="section-header">
                <h2>Recent Reservations</h2>
                <a href="#" class="view-all">View All</a>
            </div>

            <div class="table-responsive">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Court</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($RecentReservations as $reservation)

                            <tr>

                                {{-- Customer Name --}}
                                <td>
                                    {{ $reservation->user->name ?? 'Unknown User' }}
                                </td>

                                {{-- Court Name --}}
                                <td>
                                    {{ $reservation->court->name ?? 'Unknown Court' }}
                                </td>

                                {{-- Booking Date --}}
                                <td>
                                    {{ \Carbon\Carbon::parse($reservation->booking_date)->format('M d, Y') }}
                                </td>

                                {{-- Booking Time --}}
                                <td>
                                    {{ \Carbon\Carbon::parse($reservation->start_time)->format('h:i A') }}
                                    -
                                    {{ \Carbon\Carbon::parse($reservation->end_time)->format('h:i A') }}
                                </td>

                                {{-- Booking Status --}}
                                <td>
                                    <span class="status-badge status-{{ strtolower($reservation->booking_status) }}">
                                        {{ ucfirst($reservation->booking_status) }}
                                    </span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="empty-state">
                                    No reservations found.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection