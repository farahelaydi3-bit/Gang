@extends('admins.layout.app')

@section('title', __('Reservations'))

@section('content')

    <div class="reservations-page">

        <x-super-admin.page-header title="{{ __('Reservations') }}" description="{{ __('Find court bookings and manage their status.') }}" />
        <x-super-admin.form-errors />

        <div class="stats-grid" aria-label="{{ __('Reservation totals') }}">
            @foreach (['pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled', 'completed' => 'Completed'] as $status => $label)
                <div class="stat-card"><div class="stat-info"><span>{{ __($label) }}</span><strong>{{ $reservationStats->{$status} ?? 0 }}</strong></div></div>
            @endforeach
        </div>

        <section class="admin-card super-admin-filter" aria-label="{{ __('Filter reservations') }}">
            <form action="{{ route('admin.reservations.index') }}" method="GET" class="super-admin-filter-form">
                <div class="super-admin-filter-field super-admin-filter-field--search">
                    <label class="form-label" for="reservation-search">{{ __('Search') }}</label>
                    <input class="form-control" id="reservation-search" name="search" type="search" placeholder="{{ __('Customer name') }}" value="{{ request('search') }}">
                </div>
                <div class="super-admin-filter-field">
                    <label class="form-label" for="reservation-status">{{ __('Status') }}</label>
                    <select id="reservation-status" name="status" class="form-control">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (['pending', 'confirmed', 'cancelled', 'completed'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ __(ucfirst($status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="super-admin-filter-field">
                    <label class="form-label" for="reservation-type">{{ __('Booking type') }}</label>
                    <select id="reservation-type" name="type" class="form-control">
                        <option value="">{{ __('All types') }}</option>
                        @foreach (['individual', 'group', 'subscription'] as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ __(ucfirst($type)) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="admin-button">{{ __('Filter') }}</button>
                <a href="{{ route('admin.reservations.index') }}" class="admin-button admin-button-outline">{{ __('Reset') }}</a>
            </form>
        </section>

        <div class="admin-card super-admin-table-card">
            <div class="admin-card-header"><h2>{{ __('All reservations') }}</h2></div>
            {{-- Reservations Table --}}
            <div class="admin-table-wrap">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Court') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Time') }}</th>
                            <th>{{ __('Duration') }}</th>
                            <th>{{ __('Price') }}</th>
                            <th>{{ __('Deposit') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
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
                                    {{ $reservation->user?->name ?? $reservation->creatorAdmin?->name ?? 'Group game' }}
                                </td>

                                <td>
                                    {{ __(ucfirst($reservation->booking_type)) }}
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
                                    {{ $reservation->duration_minutes }} {{ __('min') }}
                                </td>

                                <td>
                                    {{ number_format($reservation->total_price, 2) }} {{ __('JOD') }}
                                </td>

                                <td>
                                    {{ number_format($reservation->deposit_amount, 2) }} {{ __('JOD') }}
                                </td>

                                <td>
                                    <x-dashboard-status :state="$reservation->booking_status" />
                                </td>

                                <td>
                                    <div class="reservation-actions">

                                        {{-- Confirm --}}
                                        @if($reservation->booking_status === 'pending')
                                            <form action="{{ route('admin.reservations.confirm', $reservation->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="admin-button">
                                                    {{ __('Confirm') }}
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Complete --}}
                                        @if($reservation->booking_status === 'confirmed')
                                            <form action="{{ route('admin.reservations.complete', $reservation->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="admin-button admin-button-outline">
                                                    {{ __('Complete') }}
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Cancel --}}
                                        @if(in_array($reservation->booking_status, ['pending', 'confirmed']))
                                            <form action="{{ route('admin.reservations.cancel', $reservation->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="admin-button admin-button-danger">
                                                    {{ __('Cancel') }}
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
                                        <h3>{{ __('No Reservations Found') }}</h3>
                                        <p>{{ __('There are no reservations matching your search.') }}</p>
                                    </div>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}
            <div class="reservations-pagination">
                {{ $reservations->withQueryString()->links('components.super-admin.pagination') }}
            </div>

        </div>

    </div>

@endsection
