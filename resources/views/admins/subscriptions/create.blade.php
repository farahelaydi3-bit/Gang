@extends('admins.layout.app')

@section('page-title', 'Create Subscription')

@section('content')

<div class="subscriptions-page">

    <div class="section-header">
        <div>
            <h2>Create Subscription</h2>
            <p>Create a subscription plan for a user. It will remain pending until payment.</p>
        </div>

        <a href="{{ route('admin.subscriptions.index') }}"
           class="admin-button-outline">
            Back to Subscriptions
        </a>
    </div>


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
                   class="admin-button-outline">
                    Cancel
                </a>

                <button type="submit" class="admin-button">
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

    updatePricePreview();
</script>

@endsection