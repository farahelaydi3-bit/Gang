@extends('layouts.demo')

@section('title', __('Waitlist'))
@section('page', 'waitlist')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Waiting list',
            'description' => 'Review the courts and times you are waiting for.',
        ])

        @if (session('success'))
            <div class="panel mb-4">{{ session('success') }}</div>
        @endif

        @auth
            <p class="mb-4"><a class="button" href="{{ route('waitlist.create') }}">{{ __('Join a waiting list') }}</a></p>
            <div class="categories-table waitlist-table" tabindex="0" role="region" aria-label="{{ __('Waiting list') }}">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Game / Court') }}</th>
                            <th>{{ __('Sport') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Time') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $item)
                            <tr>
                                <td>{{ $item->groupGame?->title ?? $item->court->name }}</td>
                                <td>{{ $item->court->sports->name ?? '—' }}</td>
                                <td>{{ $item->booking_date->format('M j, Y') }}</td>
                                <td>{{ $item->start_time }} – {{ $item->end_time }}</td>
                                <td>{{ __(ucfirst($item->status)) }}</td>
                                <td>
                                    @if ($item->status === 'offered')
                                        <form action="{{ route('waiting-list.accept', $item) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="button">{{ __('Accept offer') }}</button>
                                        </form>
                                    @endif
                                    @if ($item->pendingDeposit)
                                        <a class="button" href="{{ route('games.payment.checkout', [$item->group_game_id, $item->pendingDeposit->id]) }}">{{ __('Continue deposit') }}</a>
                                        <small>{{ __('Offer expires') }} {{ $item->offer_expires_at?->diffForHumans() }}</small>
                                    @endif
                                    @if (in_array($item->status, ['waiting', 'offered'], true)
                                        || ($item->status === 'accepted' && $item->offer_expires_at?->isFuture()))
                                        <form action="{{ route('waitlist.destroy', $item) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="button outline">{{ __('Cancel request') }}</button>
                                        </form>
                                    @endif
                                    @if ($item->smsLogs->isNotEmpty())
                                        <details>
                                            <summary>{{ __('Notification history') }} ({{ $item->smsLogs->count() }})</summary>
                                            @foreach ($item->smsLogs as $sms)
                                                <p><strong>{{ __(ucfirst($sms->status)) }}</strong>
                                                    · {{ $sms->sent_at?->format('M j, Y H:i') ?? $sms->created_at->format('M j, Y H:i') }}
                                                    · {{ $sms->message }}</p>
                                            @endforeach
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">{{ __('You are not on any waiting lists.') }}</td>
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
