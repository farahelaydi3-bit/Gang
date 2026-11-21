@props(['court', 'photo' => null, 'ownerUrl' => null])

<section class="admin-card" aria-labelledby="court-information">
    <div class="admin-card-header"><h2 id="court-information">Court information</h2></div>

    <dl class="super-admin-record-facts">
        <div>
            <dt>Owner</dt>
            <dd>
                @if ($court->admin && $ownerUrl)
                    <a class="super-admin-record-link" href="{{ $ownerUrl }}" dir="auto">{{ $court->admin->name }}</a>
                @else
                    {{ $court->admin?->name ?? '—' }}
                @endif
            </dd>
        </div>
        @foreach ([
            'Business' => $court->admin?->business_name,
            'Sport' => $court->sports?->name,
            'Address' => $court->address,
            'Status' => ucfirst($court->status),
            'Opening time' => $court->opening_time,
            'Closing time' => $court->closing_time,
            'Minimum booking' => $court->min_booking_minutes . ' minutes',
            'Weekday price' => number_format($court->weekday_price, 2) . ' JOD',
            'Weekend price' => number_format($court->weekend_price, 2) . ' JOD',
            'Evening price' => number_format($court->evening_price, 2) . ' JOD',
            'Deposit' => number_format($court->deposit_amount, 2) . ' JOD',
        ] as $label => $value)
            <div><dt>{{ $label }}</dt><dd dir="auto">{{ $value ?? '—' }}</dd></div>
        @endforeach
    </dl>
</section>

<section class="admin-card" aria-labelledby="court-photo">
    <div class="admin-card-header"><h2 id="court-photo">Court photo</h2></div>
    @if ($photo)
        <a class="super-admin-court-photo" href="{{ $photo }}" target="_blank" rel="noopener"
            aria-label="Open full-size photo of {{ $court->name }}">
            <img src="{{ $photo }}" alt="{{ $court->name }}">
        </a>
    @else
        <p class="super-admin-muted">No court photo is available.</p>
    @endif
</section>
