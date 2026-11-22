@extends('layouts.super-admin')

@section('title', __('Manage Owners'))
@section('page-title', 'Court Owners Management')

@section('content')

<x-super-admin.page-header title="{{ __('Manage Owners') }}" description="{{ __('Manage court owner accounts and approval status.') }}" />

<x-super-admin.form-errors />

<x-super-admin.list-filters :action="route('super_admin.admins.index')"
    placeholder="Name, business, email or phone"
    :selects="['status' => ['Status', 'All statuses', $statuses]]" />

{{-- Owners Table --}}
<div class="admin-card super-admin-table-card">

    <div class="admin-card-header">
        <h3>{{ __('All Court Owners (') }}{{ $admins->total() }})</h3>
    </div>

    <div class="admin-table-wrap">

        <table class="admin-table">

            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Owner Name') }}</th>
                    <th>{{ __('Business Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Registered') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($admins as $admin)

                <tr>
                    <td>{{ $admin->id }}</td>

                    <td>
                        <a class="super-admin-record-link" href="{{ route('super_admin.admins.show', $admin) }}" dir="auto">
                            {{ $admin->name }}
                        </a>
                    </td>

                    <td>
                        {{ $admin->business_name ?? '—' }}
                    </td>

                    <td>{{ $admin->email }}</td>

                    <td>{{ $admin->phone ?? '—' }}</td>

                    <td>
                        @if ($admin->status === 'active')
                        <span class="badge badge-success">
                            {{ __('Active') }}
                        </span>

                        @elseif ($admin->status === 'pending')
                        <span class="badge badge-warning">
                            {{ __('Pending') }}
                        </span>

                        @elseif ($admin->status === 'rejected')
                        <span class="badge badge-danger">
                            {{ __('Rejected') }}
                        </span>

                        @elseif ($admin->status === 'suspended')
                        <span class="badge badge-danger">
                            {{ __('Suspended') }}
                        </span>

                        @else
                        <span class="badge">
                            {{ __('Unknown') }}
                        </span>
                        @endif
                    </td>

                    <td>
                        {{ $admin->created_at?->format('Y-m-d') ?? '—' }}
                    </td>

                    <td>
                        <form
                            method="POST"
                            action="{{ route('super_admin.admins.status', $admin->id) }}"
                            class="super-admin-inline-actions">

                            @csrf
                            @method('PATCH')

                            <select name="status"
                                class="form-control"
                                aria-label="{{ __('Owner status') }}">

                                <option value="pending"
                                    @selected($admin->status === 'pending')>
                                    {{ __('Pending') }}
                                </option>

                                <option value="active"
                                    @selected($admin->status === 'active')>
                                    {{ __('Active') }}
                                </option>

                                <option value="rejected"
                                    @selected($admin->status === 'rejected')>
                                    {{ __('Rejected') }}
                                </option>

                                <option value="suspended"
                                    @selected($admin->status === 'suspended')>
                                    {{ __('Suspended') }}
                                </option>

                            </select>

                            <button type="submit"
                                class="admin-button admin-button-outline">
                                {{ __('Save') }}
                            </button>

                        </form>
                    </td>
                </tr>

                @empty

                <tr>
                    <td colspan="8" class="super-admin-empty">
                        {{ __(\App\Support\DashboardList::hasFilters(request()) ? 'No results match your filters.' : 'No court owners registered yet.') }}
                    </td>
                </tr>

                @endforelse
            </tbody>

        </table>

    </div>

    {{ $admins->links('components.super-admin.pagination') }}
</div>

@endsection
