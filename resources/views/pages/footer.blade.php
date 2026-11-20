<footer>
    <div class="wrap header-row">
        @include('pages.brand')
        <span>{{ __('Amman, Jordan') }}</span>
        <div class="row">
            <a href="{{ route('courtbook.courts') }}">{{ __('Explore courts') }}</a>
            <a href="{{ route('waitlist.index') }}">{{ __('Waiting list') }}</a>
            <a href="{{ route('courtbook.modals') }}">{{ __('Modal library') }}</a>
        </div>
    </div>
</footer>

