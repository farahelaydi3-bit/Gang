@extends('layouts.demo')

@php
    $bookings = auth()->check()
        ? \App\Models\Booking::query()
            ->with('court.sports')
            ->where('user_id', auth()->id())
            ->latest('booking_date')
            ->get()
        : collect();
@endphp

@section('title', __('My Bookings'))
@section('page', 'bookings')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'My bookings',
            'description' => 'View your court reservations.',
        ])

        @auth
            <div class="categories-table">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Court') }}</th>
                            <th>{{ __('Sport') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Time') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr>
                                <td>{{ $booking->court->name }}</td>
                                <td>{{ $booking->court->sports->name }}</td>
                                <td>{{ $booking->booking_date->format('M j, Y') }}</td>
                                <td>{{ $booking->start_time }} – {{ $booking->end_time }}</td>
                                <td>{{ __(ucfirst($booking->booking_status)) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">{{ __('No bookings found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <p class="muted">{{ __('Log in to view your bookings.') }}</p>
            <a class="button" href="{{ route('login') }}">{{ __('Log in') }}</a>
        @endauth
    </div>
@endsection
