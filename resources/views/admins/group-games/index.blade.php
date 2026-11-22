@extends('admins.layout.app')

@section('title', __('Group Games'))

@section('content')

    <div class="group-games-page">

        {{-- Page Header --}}
        <x-super-admin.page-header title="{{ __('Group Games') }}" description="{{ __('Manage group games and player reservations.') }}">
            <x-slot:actions>
                <a class="admin-button" href="{{ route('admin.group-games.create') }}">{{ __('+ Create group game') }}</a>
            </x-slot:actions>
        </x-super-admin.page-header>

        {{-- Statistics --}}
        <div class="group-games-stats">

            <div class="stat-card">
                <h3>{{ __('Total Games') }}</h3>
                <p>{{ $games->total() }}</p>
            </div>

            <div class="stat-card">
                <h3>{{ __('Open Games') }}</h3>
                <p>{{ $games->where('status', 'open')->count() }}</p>
            </div>

            <div class="stat-card">
                <h3>{{ __('Full Games') }}</h3>
                <p>{{ $games->where('status', 'full')->count() }}</p>
            </div>

            <div class="stat-card">
                <h3>{{ __('Cancelled Games') }}</h3>
                <p>{{ $games->where('status', 'cancelled')->count() }}</p>
            </div>

        </div>

        {{-- Games Table --}}
        <div class="admin-card group-games-table-card">

            <div class="section-header">
                <h2>{{ __('All Group Games') }}</h2>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>{{ __('Game') }}</th>
                            <th>{{ __('Court and schedule') }}</th>
                            <th>{{ __('Players') }}</th>
                            <th>{{ __('Price / Player') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($games as $game)

                            <tr>

                                {{-- Game --}}
                                <td>
                                    <div class="game-title">
                                        {{ $game->title }}
                                    </div>

                                    <small>
                                        {{ $game->description }}
                                    </small>
                                </td>

                                {{-- Court and schedule --}}
                                <td>
                                    <div>{{ $game->court?->name ?? '—' }}</div>
                                    <small>
                                        {{ $game->game_date?->format('M j, Y') }}
                                        · {{ $game->start_time }}–{{ $game->end_time }}
                                    </small>
                                </td>

                                {{-- Players --}}
                                <td>
                                    {{ $game->joined_players_count ?? 0 }}
                                    /
                                    {{ $game->max_players }}
                                </td>

                                {{-- Price --}}
                                <td>
                                    {{ number_format($game->player_price, 2) }}
                                    {{ __('JOD') }}
                                </td>

                                {{-- Status --}}
                                <td>
                                    <x-dashboard-status :state="$game->status" />
                                </td>

                                {{-- Actions --}}
                                <td>
                                    <div class="group-game-actions">

                                        <a href="{{ route('admin.group-games.edit', $game->id) }}"
                                            class="admin-button admin-button-outline">
                                            {{ __('Edit') }}
                                        </a>

                                        <form action="{{ route('admin.group-games.cancel', $game->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="admin-button admin-button-outline">
                                                {{ __('Cancel') }}
                                            </button>
                                        </form>

                                        @if(in_array($game->status, ['open', 'full'], true))
                                            <form action="{{ route('admin.group-games.complete', $game->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="admin-button admin-button-outline">{{ __('Mark completed') }}</button>
                                            </form>
                                        @endif

                                        <form action="{{ route('admin.group-games.destroy', $game->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="admin-button admin-button-danger">
                                                {{ __('Delete') }}
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6">
                                    <div class="table-empty">
                                        <h3>{{ __('No Group Games Found') }}</h3>
                                        <p>{{ __('You haven\'t created any group games yet.') }}</p>

                                        <a href="{{ route('admin.group-games.create') }}" class="admin-button">
                                            {{ __('Create Your First Game') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="admin-pagination">
                {{ $games->links('components.super-admin.pagination') }}
            </div>

        </div>

    </div>

@endsection
