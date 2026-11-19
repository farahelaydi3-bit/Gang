@forelse ($links as $name => $label)
    <a
        href="{{ route('courtbook.' . $name) }}"
        @if ($activePage === $name) aria-current="page" @endif
    >
        {{ __($label) }}
    </a>
@empty
    <span class="muted">{{ __('No links available.') }}</span>
@endforelse

