@php
    $steps = [
        ['Find a court', 'Browse facilities and sports around Amman.', 'pin'],
        ['Pick a time', 'Review availability and confirm your booking.', 'calendar'],
        ['Play with your community', 'Invite friends or join a public pickup game.', 'team'],
    ];
@endphp
<section class="section" aria-labelledby="how-it-works-title">
    <h2 id="how-it-works-title">{{ __('How CourtBook works') }}</h2>
    <div class="player-grid">
        @forelse ($steps as [$heading, $description, $icon])
            <article class="panel">
                <span class="step-icon" aria-hidden="true">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        @switch($icon)
                            @case('pin')
                                <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                <circle cx="12" cy="10" r="2.5" />
                                @break
                            @case('calendar')
                                <rect x="3" y="5" width="18" height="16" rx="2" />
                                <path d="M16 3v4M8 3v4M3 10h18m-12 5 2 2 4-4" />
                                @break
                            @case('team')
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8" />
                                <circle cx="9" cy="7" r="4" />
                                @break
                        @endswitch
                    </svg>
                </span>
                <h3>{{ __($heading) }}</h3>
                <p class="muted">{{ __($description) }}</p>
            </article>
        @empty
            <p class="muted">{{ __('No steps are available.') }}</p>
        @endforelse
    </div>
</section>

