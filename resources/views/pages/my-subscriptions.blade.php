@extends('layouts.demo')

@php
    $subscriptions = auth()->check()
        ? \App\Models\Subscription::query()
            ->where('user_id', auth()->id())
            ->with('admin')
            ->latest('start_date')
            ->get()
        : collect();
@endphp

@section('title', __('My Subscriptions'))
@section('page', 'my-subscriptions')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'My subscriptions',
            'description' => 'Review your subscription hours and status.',
        ])

        @auth
            <div class="categories-table">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Package') }}</th>
                            <th>{{ __('Hours') }}</th>
                            <th>{{ __('Used') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Period') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subscriptions as $subscription)
                            <tr>
                                <td>{{ $subscription->package_name }}</td>
                                <td>{{ $subscription->total_hours }}</td>
                                <td>{{ $subscription->used_hours }}</td>
                                <td>{{ number_format($subscription->amount, 2) }} JOD</td>
                                <td>
                                    {{ $subscription->start_date->format('M j, Y') }} –
                                    {{ $subscription->end_date->format('M j, Y') }}
                                </td>
                                <td>{{ __(ucfirst($subscription->status)) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">{{ __('No subscriptions found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <p class="muted">{{ __('Log in to view your subscriptions.') }}</p>
            <a class="button" href="{{ route('login') }}">{{ __('Log in') }}</a>
        @endauth
    </div>
@endsection
