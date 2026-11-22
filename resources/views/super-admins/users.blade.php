@extends('layouts.super-admin')

@section('title', __('Manage Players'))
@section('page-title', 'Players Management')

@section('content')

<x-super-admin.page-header title="{{ __('Manage Players') }}" description="{{ __('Manage registered players and their account status.') }}" />

<x-super-admin.form-errors />

{{-- Players Table --}}
<div class="admin-card super-admin-table-card">

    <div class="admin-card-header">
        <h3>{{ __('All Players (') }}{{ $users->count() }})</h3>
    </div>

    <div class="admin-table-wrap">

        <table class="admin-table">

            <thead>
                <tr>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('Player Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Registered') }}</th>
                    <th>{{ __('Actions') }}</th>
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
                            {{ __('Active') }}
                        </span>

                        @elseif ($user->status === 'suspended')
                        <span class="badge badge-danger">
                            {{ __('Suspended') }}
                        </span>

                        @else
                        <span class="badge">
                            {{ __(ucfirst($user->status ?? 'Unknown')) }}
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
                                aria-label="{{ __('Player status') }}">

                                <option value="active"
                                    @selected($user->status === 'active')>
                                    {{ __('Active') }}
                                </option>

                                <option value="suspended"
                                    @selected($user->status === 'suspended')>
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
                    <td colspan="7" class="super-admin-empty">
                        {{ __('No players registered yet.') }}
                    </td>
                </tr>

                @endforelse
            </tbody>

        </table>

    </div>

</div>

@endsection
