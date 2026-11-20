
@extends('admins.layout.app')

@section('page-title', 'Edit Subscription')

@section('content')

<div class="subscriptions-page">

    <div class="section-header">
        <div>
            <h1>Edit Subscription</h1>
            <p>Update subscription details and pricing.</p>
        </div>

        <a href="{{ route('admin.subscriptions.index') }}"
           class="admin-button admin-button-outline">
            Back
        </a>
    </div>

    <div class="admin-card subscription-form-card">

        <form action="{{ route('admin.subscriptions.update', $subscription->id) }}"
              method="POST">

            @csrf
            @method('PUT')

            <div class="subscription-form-grid">

                {{-- Subscription Name --}}
                <div class="form-group">
                    <label class="form-label">Subscription Name</label>

                    <input type="text"
                           name="package_name"
                           class="form-control"
                           value="{{ old('package_name', $subscription->package_name) }}"
                           placeholder="e.g. Monthly Subscription"
                           required>

                    @error('package_name')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Total Hours --}}
                <div class="form-group">
                    <label class="form-label">Total Hours</label>

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
                    <label class="form-label">Price (JOD)</label>

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
                    <label class="form-label">Discount (%)</label>

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
                    <label class="form-label">Duration (Days)</label>

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
                <span>Final Price</span>
                <h2><span id="final-price">0.00</span> JOD</h2>
                <small id="discount-message"></small>
            </div>

            <div class="subscription-form-actions">

                <a href="{{ route('admin.subscriptions.index') }}"
                   class="admin-button admin-button-outline">
                    Cancel
                </a>

                <button type="submit" class="admin-button">
                    Save Changes
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

    function updatePricePreview() {
        const price = parseFloat(priceInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;

        const finalPrice = price - (price * discount / 100);

        finalPriceElement.textContent = finalPrice.toFixed(2);

        if (discount > 0) {
            discountMessage.textContent =
                `${discount}% discount applied`;
        } else {
            discountMessage.textContent = 'No discount applied';
        }
    }

    priceInput.addEventListener('input', updatePricePreview);
    discountInput.addEventListener('input', updatePricePreview);

    updatePricePreview();
</script>
@endsection
