@extends('admins.layout.app')

@section('content')

    <div class="dashboard-wrapper">

        {{-- Page Header --}}
        <div class="dashboard-header">
            <div>
                <h1>My Courts</h1>
                <p>Manage your courts, prices, and approval status.</p>
            </div>

            <a href="{{ route('admin.courts.create') }}" class="admin-button">
                + Add New Court
            </a>
        </div>


        {{-- Success / Error Messages --}}
        @if (session('success'))
            <div class="admin-card" style="margin-bottom: 20px; color: var(--success-text);">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="admin-card" style="margin-bottom: 20px; color: var(--danger-text);">
                {{ session('error') }}
            </div>
        @endif


        {{-- Courts Grid --}}
        <div class="courts-grid">

            @forelse ($courts as $court)

                <div class="court-card">

                    {{-- Court Image --}}
                   {{-- Court Image --}}
<div class="court-image">

    @if ($court->image)
        <img
            src="{{ \Illuminate\Support\Str::startsWith($court->image, 'images/')
                ? asset($court->image)
                : asset('storage/' . $court->image) }}"
            alt="{{ $court->name }}">
    @else
        <div class="court-no-image">
            🏟️
        </div>
    @endif

</div>


                    {{-- Court Details --}}
                    <div class="court-details">

                        <h3>{{ $court->name }}</h3>

                        <p class="court-sport">
                            {{ $court->sports->name ?? 'Unknown Sport' }}
                        </p>

                        <p class="court-address">
                            📍 {{ $court->address }}
                        </p>


                        {{-- Prices --}}
                        <div class="court-prices">
                            <p>
                                <strong>Weekday:</strong>
                                {{ number_format($court->weekday_price, 2) }} JOD
                            </p>

                            <p>
                                <strong>Weekend:</strong>
                                {{ number_format($court->weekend_price, 2) }} JOD
                            </p>

                            <p>
                                <strong>Evening:</strong>
                                {{ number_format($court->evening_price, 2) }} JOD
                            </p>
                        </div>


                        {{-- Deposit --}}
                        <p class="court-deposit">
                            <strong>Deposit:</strong>
                            {{ number_format($court->deposit_amount, 2) }} JOD
                        </p>


                        {{-- Status --}}
                        @if ($court->status === 'approved')
                            <span class="court-status badge-success">
                                Approved
                            </span>

                        @elseif ($court->status === 'pending')
                            <span class="court-status badge-warning">
                                Pending Approval
                            </span>

                        @elseif ($court->status === 'rejected')
                            <span class="court-status badge-danger">
                                Rejected
                            </span>

                        @else
                            <span class="court-status badge">
                                {{ ucfirst($court->status) }}
                            </span>
                        @endif


                        {{-- Actions --}}
                        <div style="display: flex; gap: 10px; margin-top: 18px;">

                            <a href="{{ route('admin.courts.edit', $court->id) }}"
                                class="admin-button admin-button-outline admin-button-sm">
                                Edit
                            </a>

                            <form action="{{ route('admin.courts.destroy', $court->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this court?');">

                                @csrf
                                @method('DELETE')

                                <button type="submit" style="background-color: red;"
                                    class="admin-button admin-button-danger admin-button-sm">
                                    Delete
                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            @empty

                {{-- Empty State --}}
                <div class="empty-state" style="grid-column: 1 / -1;">

                    <div class="empty-icon">
                        🏟️
                    </div>

                    <h3>No Courts Yet</h3>

                    <p>
                        You haven't added any courts yet.
                        Start by adding your first court.
                    </p>

                    <a href="{{ route('admin.courts.create') }}" class="admin-button" style="margin-top: 20px;">
                        + Add Your First Court
                    </a>

                </div>

            @endforelse

        </div>
    </div>

@endsection