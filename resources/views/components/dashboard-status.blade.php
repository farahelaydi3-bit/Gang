@props(['state', 'label' => null])

@php
    $tone = match ($state) {
        'active', 'approved', 'open', 'confirmed', 'completed', 'sent', 'delivered' => 'badge-success',
        'pending', 'full' => 'badge-warning',
        'cancelled', 'expired', 'failed', 'inactive', 'rejected', 'suspended' => 'badge-danger',
        default => '',
    };
@endphp

<span class="badge {{ $tone }}">{{ $label ?? ucfirst($state ?? 'Unknown') }}</span>
