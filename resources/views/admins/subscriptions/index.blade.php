@extends('admins.layout.app')

@section('page-title', 'Subscriptions')

@section('content')

    <div class="subscriptions-page">

        {{-- Page Header --}}
        <div class="section-header">
            <div>
                <h2>Subscriptions Management</h2>
                <p>Manage subscriptions and monitor their status.</p>
            </div>

            <a href="{{ route('admin.subscriptions.create') }}" class="admin-button">
                + Create Subscription
            </a>
        </div>


        {{-- Statistics --}}
        <div class="subscriptions-stats">

            <div class="stat-card">
                <span>Total Subscriptions</span>
                <h3>{{ $subscriptionStats->total ?? 0 }}</h3>
            </div>

            <div class="stat-card">
                <span>Active</span>
                <h3>{{ $subscriptionStats->active ?? 0 }}</h3>
            </div>

            <div class="stat-card">
                <span>Pending</span>
                <h3>{{ $subscriptionStats->pending ?? 0 }}</h3>
            </div>

            <div class="stat-card">
                <span>Expired</span>
                <h3>{{ $subscriptionStats->expired ?? 0 }}</h3>
            </div>

            <div class="stat-card">
                <span>Cancelled</span>
                <h3>{{ $subscriptionStats->cancelled ?? 0 }}</h3>
            </div>

        </div>


        {{-- Alerts --}}
        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-danger">
                {{ session('error') }}
            </div>
        @endif


        {{-- Search & Filter --}}
        <div class="admin-card subscriptions-filter-card">

            <form action="{{ route('admin.subscriptions.index') }}" method="GET" class="subscriptions-filter-form">

                <div class="form-group search-group">
                    <label class="form-label" for="search">
                        Search Subscriptions
                    </label>

                    <input type="text" name="search" id="search" class="form-control"
                        placeholder="Package name, user name, or email..." value="{{ request('search') }}">
                </div>

                <div class="form-group status-filter-group">
                    <label class="form-label" for="status">
                        Status
                    </label>

                    <select name="status" id="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                            Pending
                        </option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>
                            Expired
                        </option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                            Cancelled
                        </option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="admin-button">
                        Filter
                    </button>

                    <a href="{{ route('admin.subscriptions.index') }}" class="admin-button-outline">
                        Reset
                    </a>
                </div>

            </form>

        </div>


        {{-- Subscriptions Table --}}
        <div class="admin-card">

            <div class="admin-table-wrap">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Package</th>
                            <th>Total Hours</th>
                            <th>Used Hours</th>
                            <th>Amount</th>
                            <th>Duration</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($subscriptions as $subscription)

                            <tr>

                                {{-- ID --}}
                                <td>{{ $subscription->id }}</td>

                                {{-- User --}}
                                <td>
                                    @if($subscription->user)
                                        <strong>{{ $subscription->user->name }}</strong>
                                        <small>{{ $subscription->user->email }}</small>
                                    @else
                                        <span>Deleted User</span>
                                    @endif
                                </td>

                                {{-- Package --}}
                                <td>
                                    {{ $subscription->package_name }}
                                </td>

                                {{-- Total Hours --}}
                                <td>
                                    {{ number_format($subscription->total_hours, 2) }}
                                </td>

                                {{-- Used Hours --}}
                                <td>
                                    {{ number_format($subscription->used_hours, 2) }}
                                </td>

                                {{-- Amount --}}
                                <td>
                                    {{ number_format($subscription->amount, 2) }} JOD
                                </td>

                                {{-- Duration --}}
                                <td>
                                    {{ $subscription->duration_days }} Days
                                </td>

                                {{-- Start Date --}}
                                <td>
                                    {{ $subscription->start_date->format('d M Y') }}
                                </td>

                                {{-- End Date --}}
                                <td>
                                    {{ $subscription->end_date->format('d M Y') }}
                                </td>

                                {{-- Status --}}
                                <td>
                                    <span class="badge status-badge badge-{{ $subscription->status }}">
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td>

                                    <div class="subscription-actions">

                                        <a href="{{ route('admin.subscriptions.show', $subscription->id) }}"
                                            class="admin-button admin-button-sm admin-button-outline">
                                            View
                                        </a>

                                        <a href="{{ route('admin.subscriptions.edit', $subscription->id) }}"
                                            class="admin-button admin-button-sm admin-button-edit">
                                            Edit
                                        </a>

                                        <form action="{{ route('admin.subscriptions.destroy', $subscription->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this subscription?');">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="admin-button admin-button-sm admin-button-danger">
                                                Delete
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="11">
                                    <div class="table-empty">
                                        No subscriptions found.
                                    </div>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}
            @if($subscriptions->hasPages())
                <div class="admin-pagination">
                    {{ $subscriptions->links() }}
                </div>
            @endif

        </div>

    </div>

@endsection