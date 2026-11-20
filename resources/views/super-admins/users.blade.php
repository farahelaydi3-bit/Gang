@extends('layouts.super-admin')

@section('title', 'Manage Players')
@section('page-title', 'Players Management')

@section('content')

<div class="page-header">
    <div>
        <h2>Manage Players</h2>
        <p>View and manage all registered CourtBook players.</p>
    </div>

    <a href="{{ route('super_admin.dashboard') }}"
        class="admin-button admin-button-outline">
        Back to Dashboard
    </a>
</div>

{{-- Validation Errors --}}
@if ($errors->any())
<div class="admin-card"
    role="alert"
    style="margin-bottom:20px;border-color:var(--admin-danger);color:var(--admin-danger)">

    @foreach ($errors->all() as $error)
    <p style="margin:0 0 6px">
        {{ $error }}
    </p>
    @endforeach

</div>
@endif

{{-- Players Table --}}
<div class="admin-card">

    <div class="admin-card-header">
        <h3>All Players ({{ $users->count() }})</h3>
    </div>

    <div class="admin-table-wrap">

        <table class="admin-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Player Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($users as $user)

                <tr>
                    <td>{{ $user->id }}</td>

                    <td>
                        <strong>{{ $user->name }}</strong>
                    </td>

                    <td>{{ $user->email }}</td>

                    <td>{{ $user->phone ?? '—' }}</td>

                    <td>
                        @if ($user->status === 'active')
                        <span class="badge badge-success">
                            Active
                        </span>

                        @elseif ($user->status === 'suspended')
                        <span class="badge badge-danger">
                            Suspended
                        </span>

                        @else
                        <span class="badge">
                            {{ ucfirst($user->status ?? 'Unknown') }}
                        </span>
                        @endif
                    </td>

                    <td>
                        {{ $user->created_at?->format('Y-m-d') ?? '—' }}
                    </td>

                    <td>
                        <form
                            method="POST"
                            action="{{ route('super_admin.users.status', $user->id) }}"
                            style="display:flex;gap:8px;align-items:center;min-width:220px">

                            @csrf
                            @method('PATCH')

                            <select name="status"
                                class="form-control"
                                aria-label="Player status">

                                <option value="active"
                                    @selected($user->status === 'active')>
                                    Active
                                </option>

                                <option value="suspended"
                                    @selected($user->status === 'suspended')>
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
                    <td colspan="7" style="text-align:center;padding:32px">
                        No players registered yet.
                    </td>
                </tr>

                @endforelse
            </tbody>

        </table>

    </div>

</div>

@endsection