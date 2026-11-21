@extends('layouts.demo')

@section('title', __('Player Terms & Conditions'))
@section('description', __('Clear terms for booking courts and joining games on Gang.'))
@section('page', 'player-terms')

@section('content')
    @php
        $terms = [
            ['number' => '01', 'kicker' => 'YOUR ACCOUNT', 'title' => 'Keep your account details accurate', 'body' => 'Use your own account and provide details that let you manage your bookings. Keep your sign-in information private and tell us if you think someone else has accessed your account.'],
            ['number' => '02', 'kicker' => 'BOOKINGS', 'title' => 'Check the details before you confirm', 'body' => 'Review the court, date, start and end time, duration, and price before submitting a booking. Availability is not reserved until the payment step is completed and the booking is confirmed.'],
            ['number' => '03', 'kicker' => 'PAYMENTS', 'title' => 'Know what you are paying', 'body' => 'The booking summary shows the total price, deposit due, and any remaining balance. Complete the payment shown for your booking to confirm it. If the local demo payment mode is enabled, the card screen is a simulation and no real payment is processed.'],
            ['number' => '04', 'kicker' => 'CANCELLATIONS', 'title' => 'Cancel before the booking starts', 'body' => 'You can request cancellation from My Bookings before the booked time begins. The deposit refund condition shown on the checkout page applies: the deposit is refundable only when you cancel within 24 hours after the booking is confirmed. A cancellation request does not mean a refund has already appeared in your payment method.'],
            ['number' => '05', 'kicker' => 'PUBLIC GAMES', 'title' => 'Join games you can attend', 'body' => 'When joining a public game, check its time, court, and available places first. If your plans change, update your participation so other players can use the open spot.'],
            ['number' => '06', 'kicker' => 'PLAY FAIR', 'title' => 'Respect players and venues', 'body' => 'Use Gang to arrange genuine sports activity. Treat other players and venue staff respectfully, follow the venue’s on-site rules, and do not post misleading, abusive, or disruptive content.'],
            ['number' => '07', 'kicker' => 'THE PLATFORM', 'title' => 'Gang helps you find and arrange games', 'body' => 'Court facilities, opening hours, and on-site services are provided by the venue. Check the venue details before travelling. We may update these terms or platform features; the latest version will be shown on this page.'],
        ];
    @endphp

    <div class="wrap page player-terms-page">
        <a class="terms-back-link" href="{{ route('courtbook.home') }}"><span aria-hidden="true">←</span>{{ __('Back to home') }}</a>

        <header class="player-terms-hero">
            <span class="player-terms-kicker"><i></i>{{ __('FOR PLAYERS') }}</span>
            <h1>{{ __('Terms for playing on Gang') }}</h1>
            <p>{{ __('A few clear points to keep booking and playing smooth for everyone.') }}</p>
            <div class="player-terms-meta">
                <span><i aria-hidden="true">✓</i>{{ __('For Gang players') }}</span>
                <time datetime="{{ now()->toDateString() }}">{{ __('Updated') }} · {{ now()->translatedFormat('F j, Y') }}</time>
            </div>
            <span class="player-terms-ball player-terms-ball-one" aria-hidden="true">⚽</span>
            <span class="player-terms-ball player-terms-ball-two" aria-hidden="true">🏀</span>
        </header>

        <div class="player-terms-intro">
            <span aria-hidden="true">✦</span>
            <p>{{ __('By creating an account or using Gang to book a court or join a game, you agree to follow these terms.') }}</p>
        </div>

        <div class="player-terms-grid">
            @foreach ($terms as $term)
                <article class="player-terms-card">
                    <div class="player-terms-card-top">
                        <span class="player-terms-number">{{ $term['number'] }}</span>
                        <span class="player-terms-card-kicker">{{ __($term['kicker']) }}</span>
                    </div>
                    <h2>{{ __($term['title']) }}</h2>
                    <p>{{ __($term['body']) }}</p>
                </article>
            @endforeach
        </div>

        <div class="player-terms-note">
            <span aria-hidden="true">i</span>
            <p>{{ __('Before you head out, check your booking confirmation for the latest court and time details.') }}</p>
        </div>

        <div class="player-terms-footer">
            <span>{{ __('Ready to play?') }}</span>
            <a class="button" href="{{ route('courtbook.courts') }}">{{ __('Find a court') }} <span aria-hidden="true">↗</span></a>
        </div>
    </div>
@endsection
