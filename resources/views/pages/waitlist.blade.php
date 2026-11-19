@extends('layouts.demo')

@php
    $waitingItems = auth()->check()
        ? \Illuminate\Support\Facades\DB::table('waiting_list')
            ->join('courts', 'waiting_list.court_id', '=', 'courts.id')
            ->join('sports', 'courts.sport_id', '=', 'sports.id')
            ->where('waiting_list.user_id', auth()->id())
            ->select([
                'waiting_list.*',
                'courts.name as court_name',
                'courts.address as court_address',
                'sports.name as sport_name',
            ])
            ->latest('waiting_list.created_at')
            ->get()
        : collect();
@endphp

@section('title', __('Waitlist'))
@section('page', 'waitlist')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Waiting list',
            'description' => 'Review the courts and times you are waiting for.',
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
                        @forelse ($waitingItems as $item)
                            <tr>
                                <td>{{ $item->court_name }}</td>
                                <td>{{ $item->sport_name }}</td>
                                <td>{{ \Illuminate\Support\Carbon::parse($item->booking_date)->format('M j, Y') }}</td>
                                <td>{{ $item->start_time }} – {{ $item->end_time }}</td>
                                <td>{{ __(ucfirst($item->status)) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">{{ __('You are not on any waiting lists.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <p class="muted">{{ __('Log in to view your waiting lists.') }}</p>
            <a class="button" href="{{ route('login') }}">{{ __('Log in') }}</a>
        @endauth
    </div>
@endsection
