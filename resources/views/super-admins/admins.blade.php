@extends('layouts.super-admin')

@section('title', 'Manage Owners')
@section('page-title', 'Court Owners Management')

@section('content')

<x-super-admin.page-header title="Manage Owners" description="Manage court owner accounts and approval status." />

<x-super-admin.form-errors />

{{-- Owners Table --}}
<div class="admin-card super-admin-table-card">

    <div class="admin-card-header">
        <h3>All Court Owners ({{ $admins->count() }})</h3>
    </div>

    <div class="admin-table-wrap">

        <table class="admin-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Owner Name</th>
                    <th>Business Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
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
                            Active
                        </span>

                        @elseif ($admin->status === 'pending')
                        <span class="badge badge-warning">
                            Pending
                        </span>

                        @elseif ($admin->status === 'rejected')
                        <span class="badge badge-danger">
                            Rejected
                        </span>

                        @elseif ($admin->status === 'suspended')
                        <span class="badge badge-danger">
                            Suspended
                        </span>

                        @else
                        <span class="badge">
                            Unknown
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
                                aria-label="Owner status">

                                <option value="pending"
                                    @selected($admin->status === 'pending')>
                                    Pending
                                </option>

                                <option value="active"
                                    @selected($admin->status === 'active')>
                                    Active
                                </option>

                                <option value="rejected"
                                    @selected($admin->status === 'rejected')>
                                    Rejected
                                </option>

                                <option value="suspended"
                                    @selected($admin->status === 'suspended')>
                                    Suspended
                                </option>

                            </select>

                            <button type="submit"
                                class="admin-button admin-button-outline">
                                Save
                            </button>

                        </form>
                    </td>
                </tr>

                @empty

                <tr>
                    <td colspan="8" class="super-admin-empty">
                        No court owners registered yet.
                    </td>
                </tr>

                @endforelse
            </tbody>

        </table>

    </div>

</div>

@endsection
