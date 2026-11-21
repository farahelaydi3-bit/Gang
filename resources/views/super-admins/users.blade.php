@extends('layouts.super-admin')

@section('title', 'Manage Players')
@section('page-title', 'Players Management')

@section('content')

<x-super-admin.page-header title="Manage Players" description="Manage registered players and their account status." />

<x-super-admin.form-errors />

{{-- Players Table --}}
<div class="admin-card super-admin-table-card">

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
                            class="super-admin-inline-actions">

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
                    <td colspan="7" class="super-admin-empty">
                        No players registered yet.
                    </td>
                </tr>

                @endforelse
            </tbody>

        </table>

    </div>

</div>

@endsection
