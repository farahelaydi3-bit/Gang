@extends('admins.layout.app')

@section('title', 'Create Subscription')
@section('html-lang', 'en')

@section('content')

<div class="subscriptions-page" lang="en" dir="ltr">

    <x-super-admin.page-header title="Create Subscription" description="Create a subscription plan that remains pending until payment.">
        <x-slot:actions>
            <a class="admin-button admin-button-outline" href="{{ route('admin.subscriptions.index') }}">Back to subscriptions</a>
        </x-slot:actions>
    </x-super-admin.page-header>


    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert-danger">
            <strong>Please fix the following errors:</strong>

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
                    <label for="user_id" class="form-label">Select User</label>

                    <select name="user_id" id="user_id" class="form-control" required>
                        <option value="">Choose a user</option>

                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} — {{ $user->email }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Court --}}
                <div class="form-group">
                    <label for="court_id" class="form-label">Court</label>

                    <select name="court_id" id="court_id" class="form-control" required @disabled($courts->isEmpty())>
                        <option value="">Choose one of your courts</option>

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
                            <strong id="court-preview-name">Choose a court</strong>
                            <span id="court-preview-address">Only courts managed by your account are listed.</span>
                        </span>
                    </div>
                </div>


                {{-- Package Name --}}
                <div class="form-group">
                    <label for="package_name" class="form-label">Package Name</label>

                    <input type="text"
                           name="package_name"
                           id="package_name"
                           class="form-control"
                           value="{{ old('package_name') }}"
                           placeholder="e.g. Gold Package"
                           required>
                </div>


                {{-- Total Hours --}}
                <div class="form-group">
                    <label for="total_hours" class="form-label">Total Hours</label>

                    <input type="number"
                           name="total_hours"
                           id="total_hours"
                           class="form-control"
                           min="1"
                           step="0.01"
                           value="{{ old('total_hours') }}"
                           placeholder="Enter total hours"
                           required>
                </div>


                {{-- Base Amount --}}
                <div class="form-group">
                    <label for="amount" class="form-label">Base Price (JOD)</label>

                    <input type="number"
                           name="amount"
                           id="amount"
                           class="form-control"
                           min="0"
                           step="0.01"
                           value="{{ old('amount') }}"
                           placeholder="Enter package price"
                           required>
                </div>


                {{-- Discount --}}
                <div class="form-group">
                    <label for="discount_percentage" class="form-label">
                        Discount (%)
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
                        Duration (Days)
                    </label>

                    <input type="number"
                           name="duration_days"
                           id="duration_days"
                           class="form-control"
                           min="1"
                           value="{{ old('duration_days') }}"
                           placeholder="e.g. 30"
                           required>
                </div>

            </div>


            {{-- Price Preview --}}
            <div class="subscription-price-preview">

                <div>
                    <span>Base Price</span>
                    <strong id="base-price-preview">0.00 JOD</strong>
                </div>

                <div>
                    <span>Discount</span>
                    <strong id="discount-preview">0%</strong>
                </div>

                <div>
                    <span>Final Price</span>
                    <strong id="final-price-preview">0.00 JOD</strong>
                </div>

            </div>


            {{-- Pending Notice --}}
            @if ($courts->isEmpty())
                <div class="subscription-courts-empty" role="status">
                    <strong>No courts available yet.</strong>
                    Add one of your courts before creating a subscription.
                    <a href="{{ route('admin.courts.create') }}">Add a court</a>
                </div>
            @endif

            <div class="subscription-pending-notice">
                <strong>Payment Required</strong>
                <p>
                    This subscription will be created as pending.
                    The user must complete payment before the subscription becomes active.
                </p>
            </div>


            {{-- Buttons --}}
            <div class="subscription-form-actions">

                <a href="{{ route('admin.subscriptions.index') }}"
                   class="admin-button admin-button-outline">
                    Cancel
                </a>

                <button type="submit" class="admin-button" @disabled($courts->isEmpty())>
                    Create Subscription
                </button>

            </div>

        </form>

    </div>

</div>


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
            courtPreviewName.textContent = 'Choose a court';
            courtPreviewAddress.textContent = 'Only courts managed by your account are listed.';
            return;
        }

        courtPreviewName.textContent = selectedCourt.dataset.name || selectedCourt.textContent.trim();
        courtPreviewAddress.textContent = selectedCourt.dataset.address || 'Court selected for this subscription.';
    }

    function updatePricePreview() {
        const basePrice = parseFloat(amountInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;

        const finalPrice = basePrice - (basePrice * discount / 100);

        basePricePreview.textContent = basePrice.toFixed(2) + ' JOD';
        discountPreview.textContent = discount.toFixed(2) + '%';
        finalPricePreview.textContent = finalPrice.toFixed(2) + ' JOD';
    }

    amountInput.addEventListener('input', updatePricePreview);
    discountInput.addEventListener('input', updatePricePreview);
    courtSelect.addEventListener('change', updateCourtPreview);

    updatePricePreview();
    updateCourtPreview();
</script>

@endsection