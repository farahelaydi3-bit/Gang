@extends('admins.layout.app')

@section('page-title', 'Reservations')

@section('content')

    <div class="reservations-page">

        {{-- Reservation Statistics --}}
        <div class="reservation-stats">

            <div class="stat-card">
                <div class="stat-card-info">
                    <span class="stat-card-label">Pending Reservations</span>
                    <h3>{{ $reservationStats->pending ?? 0 }}</h3>
                </div>
                <div class="stat-card-icon pending-icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-info">
                    <span class="stat-card-label">Confirmed Reservations</span>
                    <h3>{{ $reservationStats->confirmed ?? 0 }}</h3>
                </div>
                <div class="stat-card-icon confirmed-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-info">
                    <span class="stat-card-label">Cancelled Reservations</span>
                    <h3>{{ $reservationStats->cancelled ?? 0 }}</h3>
                </div>
                <div class="stat-card-icon cancelled-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-card-info">
                    <span class="stat-card-label">Completed Reservations</span>
                    <h3>{{ $reservationStats->completed ?? 0 }}</h3>
                </div>
                <div class="stat-card-icon completed-icon">
                    <i class="fas fa-flag-checkered"></i>
                </div>
            </div>

        </div>


        {{-- Reservations Section --}}
        <div class="admin-card reservations-card">

            <div class="admin-card-header">
                <div>
                    <h2>Reservations</h2>
                    <p>Manage and track your court reservations.</p>
                </div>
            </div>


            {{-- Search & Filters --}}
            <form action="{{ route('admin.reservations.index') }}" method="GET" class="reservations-filters">

                <div class="reservation-search">
                    <input type="text" name="search" placeholder="Search by customer name..."
                        value="{{ request('search') }}">

                    <button type="submit" class="admin-button">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                </div>


                <div class="reservation-filter-group">

                    {{-- Status Filter --}}
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                            Pending
                        </option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>
                            Confirmed
                        </option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                            Cancelled
                        </option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                            Completed
                        </option>
                    </select>


                    {{-- Booking Type Filter --}}
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="individual" {{ request('type') == 'individual' ? 'selected' : '' }}>
                            Individual
                        </option>
                        <option value="group" {{ request('type') == 'group' ? 'selected' : '' }}>
                            Group
                        </option>
                        <option value="subscription" {{ request('type') == 'subscription' ? 'selected' : '' }}>
                            Subscription
                        </option>
                    </select>

                    <button type="submit" class="admin-button-outline">
                        Filter
                    </button>

                    <a href="{{ route('admin.reservations.index') }}" class="admin-button-outline">
                        Reset
                    </a>

                </div>

            </form>


            {{-- Reservations Table --}}
            <div class="admin-table-wrap">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Court</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Deposit</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($reservations as $reservation)

                            <tr>
                                <td>{{ $reservation->id }}</td>

                                <td>
                                    {{ $reservation->court->name ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ $reservation->user->name ?? 'N/A' }}
                                </td>

                                <td>
                                    {{ ucfirst($reservation->booking_type) }}
                                </td>

                                <td>
                                    {{ $reservation->booking_date->format('Y-m-d') }}
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($reservation->start_time)->format('h:i A') }}
                                    -
                                    {{ \Carbon\Carbon::parse($reservation->end_time)->format('h:i A') }}
                                </td>

                                <td>
                                    {{ $reservation->duration_minutes }} min
                                </td>

                                <td>
                                    {{ number_format($reservation->total_price, 2) }} JOD
                                </td>

                                <td>
                                    {{ number_format($reservation->deposit_amount, 2) }} JOD
                                </td>

                                <td>
                                    <span class="status-badge {{ $reservation->booking_status }}">
                                        {{ ucfirst($reservation->booking_status) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="reservation-actions">

                                        {{-- Confirm --}}
                                        @if($reservation->booking_status === 'pending')
                                            <form action="{{ route('admin.reservations.confirm', $reservation->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="action-btn action-confirm">
                                                    Confirm
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Complete --}}
                                        @if($reservation->booking_status === 'confirmed')
                                            <form action="{{ route('admin.reservations.complete', $reservation->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="action-btn action-complete">
                                                    Complete
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Cancel --}}
                                        @if(in_array($reservation->booking_status, ['pending', 'confirmed']))
                                            <form action="{{ route('admin.reservations.cancel', $reservation->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="action-btn action-cancel">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Final Status --}}
                                        @if(in_array($reservation->booking_status, ['cancelled', 'completed']))
                                            <span class="no-actions">—</span>
                                        @endif

                                    </div>
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="11" class="table-empty">
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-calendar-times"></i>
                                        </div>
                                        <h3>No Reservations Found</h3>
                                        <p>There are no reservations matching your search.</p>
                                    </div>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}
            <div class="reservations-pagination">
                {{ $reservations->links() }}
            </div>

        </div>

    </div>

@endsection