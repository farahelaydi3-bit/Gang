@extends('admins.layout.app')

@section('title', __('Create Subscription'))

@section('content')

<div class="subscriptions-page">

    <x-super-admin.page-header title="{{ __('Create Subscription') }}" description="{{ __('Create a subscription plan that remains pending until payment.') }}">
        <x-slot:actions>
            <a class="admin-button admin-button-outline" href="{{ route('admin.subscriptions.index') }}">{{ __('Back to subscriptions') }}</a>
        </x-slot:actions>
    </x-super-admin.page-header>


    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert-danger">
            <strong>{{ __('Please fix the following errors:') }}</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="admin-card subscription-form-card">

        <form action="{{ route('admin.subscriptions.store') }}"
              method="POST">

            @csrf

            <div class="subscription-form-grid">

                {{-- User --}}
                <div class="form-group">
                    <div class="subscription-player-search" id="subscriptionPlayerSearch"
                        data-url="{{ route('admin.subscriptions.players') }}"
                        data-next="{{ $users->nextPageUrl() }}" data-previous="{{ $users->previousPageUrl() }}">
                        <label for="playerLookup" class="form-label">{{ __('Search players') }}</label>
                        <div class="dashboard-filter-actions">
                            <input id="playerLookup" type="search" class="form-control" maxlength="120" dir="auto"
                                value="{{ \App\Support\DashboardList::value(request(), 'search') }}" placeholder="{{ __('Name, email or phone') }}">
                            <button type="button" class="admin-button admin-button-outline" data-search-players>{{ __('Search') }}</button>
                        </div>
                        <p role="status" class="super-admin-muted" data-player-search-status hidden></p>
                    </div>

                    <label for="user_id" class="form-label">{{ __('Select User') }}</label>

                    <select name="user_id" id="user_id" class="form-control" required>
                        <option value="">{{ __('Choose a user') }}</option>

                        @if ($previousPlayer && ! $users->getCollection()->contains('id', $previousPlayer->id))
                            <option value="{{ $previousPlayer->id }}" selected>{{ $previousPlayer->name }} — {{ $previousPlayer->email }}</option>
                        @endif
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} — {{ $user->email }}
                            </option>
                        @endforeach
                    </select>
                    <div class="dashboard-filter-actions subscription-player-pages">
                        <button type="button" class="admin-button admin-button-outline" data-player-page="previous" @disabled(! $users->previousPageUrl())>{{ __('Previous') }}</button>
                        <button type="button" class="admin-button admin-button-outline" data-player-page="next" @disabled(! $users->nextPageUrl())>{{ __('Next') }}</button>
                    </div>
                    @error('user_id')<small class="super-admin-field-error">{{ $message }}</small>@enderror
                </div>

                {{-- Court --}}
                <div class="form-group">
                    <label for="court_id" class="form-label">{{ __('Court') }}</label>

                    <select name="court_id" id="court_id" class="form-control" required @disabled($courts->isEmpty())>
                        <option value="">{{ __('Choose one of your courts') }}</option>

                        @foreach($courts as $court)
                            <option value="{{ $court->id }}"
                                data-name="{{ $court->name }}"
                                data-address="{{ $court->address }}"
                                {{ old('court_id') == $court->id ? 'selected' : '' }}>
                                {{ $court->name }}{{ $court->address ? ' — '.$court->address : '' }}
                            </option>
                        @endforeach
                    </select>

                    @error('court_id')
                        <small class="form-error">{{ $message }}</small>
                    @enderror

                    <div class="subscription-court-preview" id="court-preview" aria-live="polite">
                        <span class="subscription-court-preview-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="10" r="2.2" stroke="currentColor" stroke-width="1.7"/></svg>
                        </span>
                        <span class="subscription-court-preview-copy">
                            <strong id="court-preview-name">{{ __('Choose a court') }}</strong>
                            <span id="court-preview-address">{{ __('Only courts managed by your account are listed.') }}</span>
                        </span>
                    </div>
                </div>


                {{-- Package Name --}}
                <div class="form-group">
                    <label for="package_name" class="form-label">{{ __('Package Name') }}</label>

                    <input type="text"
                           name="package_name"
                           id="package_name"
                           class="form-control"
                           value="{{ old('package_name') }}"
                           placeholder="{{ __('e.g. Gold Package') }}"
                           required>
                </div>


                {{-- Total Hours --}}
                <div class="form-group">
                    <label for="total_hours" class="form-label">{{ __('Total Hours') }}</label>

                    <input type="number"
                           name="total_hours"
                           id="total_hours"
                           class="form-control"
                           min="1"
                           step="0.01"
                           value="{{ old('total_hours') }}"
                           placeholder="{{ __('Enter total hours') }}"
                           required>
                </div>


                {{-- Base Amount --}}
                <div class="form-group">
                    <label for="amount" class="form-label">{{ __('Base Price (JOD)') }}</label>

                    <input type="number"
                           name="amount"
                           id="amount"
                           class="form-control"
                           min="0"
                           step="0.01"
                           value="{{ old('amount') }}"
                           placeholder="{{ __('Enter package price') }}"
                           required>
                </div>


                {{-- Discount --}}
                <div class="form-group">
                    <label for="discount_percentage" class="form-label">
                        {{ __('Discount (%)') }}
                    </label>

                    <input type="number"
                           name="discount_percentage"
                           id="discount_percentage"
                           class="form-control"
                           min="0"
                           max="100"
                           step="0.01"
                           value="{{ old('discount_percentage', 0) }}"
                           placeholder="0">
                </div>


                {{-- Duration --}}
                <div class="form-group">
                    <label for="duration_days" class="form-label">
                        {{ __('Duration (Days)') }}
                    </label>

                    <input type="number"
                           name="duration_days"
                           id="duration_days"
                           class="form-control"
                           min="1"
                           value="{{ old('duration_days') }}"
                           placeholder="{{ __('e.g. 30') }}"
                           required>
                </div>

            </div>


            {{-- Price Preview --}}
            <div class="subscription-price-preview">

                <div>
                    <span>{{ __('Base Price') }}</span>
                    <strong id="base-price-preview">{{ __('0.00 JOD') }}</strong>
                </div>

                <div>
                    <span>{{ __('Discount') }}</span>
                    <strong id="discount-preview">0%</strong>
                </div>

                <div>
                    <span>{{ __('Final Price') }}</span>
                    <strong id="final-price-preview">{{ __('0.00 JOD') }}</strong>
                </div>

            </div>


            {{-- Pending Notice --}}
            @if ($courts->isEmpty())
                <div class="subscription-courts-empty" role="status">
                    <strong>{{ __('No courts available yet.') }}</strong>
                    {{ __('Add one of your courts before creating a subscription.') }}
                    <a href="{{ route('admin.courts.create') }}">{{ __('Add a court') }}</a>
                </div>
            @endif

            <div class="subscription-pending-notice">
                <strong>{{ __('Payment Required') }}</strong>
                <p>
                    {{ __('This subscription will be created as pending. The user must complete payment before the subscription becomes active.') }}
                </p>
            </div>


            {{-- Buttons --}}
            <div class="subscription-form-actions">

                <a href="{{ route('admin.subscriptions.index') }}"
                   class="admin-button admin-button-outline">
                    {{ __('Cancel') }}
                </a>

                <button type="submit" class="admin-button" @disabled($courts->isEmpty())>
                    {{ __('Create Subscription') }}
                </button>

            </div>

        </form>

    </div>

</div>

<script>
    (() => {
        const lookup = document.getElementById('subscriptionPlayerSearch');
        const input = document.getElementById('playerLookup');
        const select = document.getElementById('user_id');
        const status = lookup.querySelector('[data-player-search-status]');
        const pageButtons = [...document.querySelectorAll('[data-player-page]')];
        let request;

        const load = async (url) => {
            if (!url) return;
            request?.abort();
            const controller = new AbortController();
            request = controller;
            status.hidden = false;
            status.textContent = @js(__('Searching for players…'));
            select.setAttribute('aria-busy', 'true');
            try {
                const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });
                if (!response.ok) throw new Error('Player search failed');
                const results = await response.json();
                if (controller.signal.aborted) return;

                const selected = select.selectedOptions[0];
                const selectedId = selected.value;
                const options = [select.options[0]];
                if (selectedId && !results.data.some(player => String(player.id) === selectedId)) options.push(selected);
                options.push(...results.data.map(player => new Option(`${player.name} — ${player.email}`, player.id)));
                select.replaceChildren(...options);
                select.value = selectedId;

                lookup.dataset.previous = results.prev_page_url ?? '';
                lookup.dataset.next = results.next_page_url ?? '';
                pageButtons.forEach(button => { button.disabled = !lookup.dataset[button.dataset.playerPage]; });
                status.textContent = results.data.length
                    ? @js(__('Players found on this page: :count')).replace(':count', results.data.length)
                    : @js(__('No matching players.'));
            } catch (error) {
                if (error.name !== 'AbortError') status.textContent = @js(__('Player search failed. Please try again.'));
            } finally {
                if (request === controller) select.removeAttribute('aria-busy');
            }
        };

        const search = () => {
            const url = new URL(lookup.dataset.url);
            const searchText = input.value.trim();
            if (searchText) url.searchParams.set('search', searchText);
            load(url);
        };

        lookup.querySelector('[data-search-players]').addEventListener('click', search);
        input.addEventListener('keydown', event => {
            if (event.key !== 'Enter' || event.isComposing) return;
            event.preventDefault();
            search();
        });
        pageButtons.forEach(button => button.addEventListener('click', () => load(lookup.dataset[button.dataset.playerPage])));
    })();
</script>


{{-- Live Price Preview --}}
<script>
    const amountInput = document.getElementById('amount');
    const discountInput = document.getElementById('discount_percentage');

    const basePricePreview = document.getElementById('base-price-preview');
    const discountPreview = document.getElementById('discount-preview');
    const finalPricePreview = document.getElementById('final-price-preview');
    const courtSelect = document.getElementById('court_id');
    const courtPreviewName = document.getElementById('court-preview-name');
    const courtPreviewAddress = document.getElementById('court-preview-address');

    function updateCourtPreview() {
        const selectedCourt = courtSelect.options[courtSelect.selectedIndex];

        if (!selectedCourt || !selectedCourt.value) {
            courtPreviewName.textContent = @js(__('Choose a court'));
            courtPreviewAddress.textContent = @js(__('Only courts managed by your account are listed.'));
            return;
        }

        courtPreviewName.textContent = selectedCourt.dataset.name || selectedCourt.textContent.trim();
        courtPreviewAddress.textContent = selectedCourt.dataset.address || @js(__('Court selected for this subscription.'));
    }

    function updatePricePreview() {
        const basePrice = parseFloat(amountInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;

        const finalPrice = basePrice - (basePrice * discount / 100);

        basePricePreview.textContent = basePrice.toFixed(2) + ' ' + @js(__('JOD'));
        discountPreview.textContent = discount.toFixed(2) + '%';
        finalPricePreview.textContent = finalPrice.toFixed(2) + ' ' + @js(__('JOD'));
    }

    amountInput.addEventListener('input', updatePricePreview);
    discountInput.addEventListener('input', updatePricePreview);
    courtSelect.addEventListener('change', updateCourtPreview);

    updatePricePreview();
    updateCourtPreview();
</script>

@endsection
