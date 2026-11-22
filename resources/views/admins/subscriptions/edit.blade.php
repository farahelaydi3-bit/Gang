
@extends('admins.layout.app')

@section('title', __('Edit Subscription'))

@section('content')

<div class="subscriptions-page" lang="en" dir="ltr">

    <x-super-admin.page-header title="{{ __('Edit Subscription') }}" description="{{ __('Update subscription details and pricing.') }}">
        <x-slot:actions>
            <a class="admin-button admin-button-outline" href="{{ route('admin.subscriptions.index') }}">{{ __('Back to subscriptions') }}</a>
        </x-slot:actions>
    </x-super-admin.page-header>

    <div class="admin-card subscription-form-card">

        <form action="{{ route('admin.subscriptions.update', $subscription->id) }}"
              method="POST">

            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="court_id" class="form-label">{{ __('Court') }}</label>
                <select name="court_id" id="court_id" class="form-control" required @disabled($courts->isEmpty())>
                    <option value="">{{ __('Choose one of your courts') }}</option>
                    @foreach($courts as $court)
                        <option value="{{ $court->id }}"
                            data-name="{{ $court->name }}"
                            data-address="{{ $court->address }}"
                            @selected(old('court_id', $subscription->court_id) == $court->id)>
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

            <div class="subscription-form-grid">

                {{-- Subscription Name --}}
                <div class="form-group">
                    <label class="form-label">{{ __('Subscription Name') }}</label>

                    <input type="text"
                           name="package_name"
                           class="form-control"
                           value="{{ old('package_name', $subscription->package_name) }}"
                           placeholder="{{ __('e.g. Monthly Subscription') }}"
                           required>

                    @error('package_name')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Total Hours --}}
                <div class="form-group">
                    <label class="form-label">{{ __('Total Hours') }}</label>

                    <input type="number"
                           name="total_hours"
                           class="form-control"
                           value="{{ old('total_hours', $subscription->total_hours) }}"
                           min="1"
                           step="0.5"
                           required>

                    @error('total_hours')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Price --}}
                <div class="form-group">
                    <label class="form-label">{{ __('Price (JOD)') }}</label>

                    <input type="number"
                           name="amount"
                           id="subscription-price"
                           class="form-control"
                           value="{{ old('amount', $subscription->amount) }}"
                           min="0"
                           step="0.01"
                           required>

                    @error('amount')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Discount --}}
                <div class="form-group">
                    <label class="form-label">{{ __('Discount (%)') }}</label>

                    <input type="number"
                           name="discount_percentage"
                           id="subscription-discount"
                           class="form-control"
                           value="{{ old('discount_percentage', $subscription->discount_percentage) }}"
                           min="0"
                           max="100"
                           step="0.01"
                           required>

                    @error('discount_percentage')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Duration --}}
                <div class="form-group">
                    <label class="form-label">{{ __('Duration (Days)') }}</label>

                    <input type="number"
                           name="duration_days"
                           class="form-control"
                           value="{{ old('duration_days', $subscription->duration_days) }}"
                           min="1"
                           required>

                    @error('duration_days')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- Price Preview --}}
            <div class="price-preview">
                <span>{{ __('Final Price') }}</span>
                <h2><span id="final-price">0.00</span> {{ __('JOD') }}</h2>
                <small id="discount-message"></small>
            </div>

            @if ($courts->isEmpty())
                <div class="subscription-courts-empty" role="status">
                    <strong>{{ __('No courts available yet.') }}</strong>
                    {{ __('Add one of your courts before updating this subscription.') }}
                    <a href="{{ route('admin.courts.create') }}">{{ __('Add a court') }}</a>
                </div>
            @endif

            <div class="subscription-form-actions">

                <a href="{{ route('admin.subscriptions.index') }}"
                   class="admin-button admin-button-outline">
                    {{ __('Cancel') }}
                </a>

                <button type="submit" class="admin-button" @disabled($courts->isEmpty())>
                    {{ __('Save Changes') }}
                </button>

            </div>

        </form>

    </div>

</div>

@endsection

@section('scripts')
<script>
    const priceInput = document.getElementById('subscription-price');
    const discountInput = document.getElementById('subscription-discount');
    const finalPriceElement = document.getElementById('final-price');
    const discountMessage = document.getElementById('discount-message');
    const courtSelect = document.getElementById('court_id');
    const courtPreviewName = document.getElementById('court-preview-name');
    const courtPreviewAddress = document.getElementById('court-preview-address');

    function updatePricePreview() {
        const price = parseFloat(priceInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;

        const finalPrice = price - (price * discount / 100);

        finalPriceElement.textContent = finalPrice.toFixed(2);

        if (discount > 0) {
            discountMessage.textContent =
                @js(__(':discount% discount applied')).replace(':discount', discount);
        } else {
            discountMessage.textContent = @js(__('No discount applied'));
        }
    }

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

    priceInput.addEventListener('input', updatePricePreview);
    discountInput.addEventListener('input', updatePricePreview);
    courtSelect.addEventListener('change', updateCourtPreview);

    updatePricePreview();
    updateCourtPreview();
</script>
@endsection
