```blade
@extends('admins.layout.app')

@section('page-title', 'Group Games')

@section('content')

    <div class="group-games-page">

        {{-- Page Header --}}
        <div class="group-games-header">
            <div>
                <h1>Group Games</h1>
                <p>Manage group games and player reservations.</p>
            </div>

            <a href="{{ route('admin.group-games.create') }}" class="admin-button">
                + Create Group Game
            </a>
        </div>

        {{-- Statistics --}}
        <div class="group-games-stats">

            <div class="stat-card">
                <h3>Total Games</h3>
                <p>{{ $games->total() }}</p>
            </div>

            <div class="stat-card">
                <h3>Open Games</h3>
                <p>{{ $games->where('status', 'open')->count() }}</p>
            </div>

            <div class="stat-card">
                <h3>Full Games</h3>
                <p>{{ $games->where('status', 'full')->count() }}</p>
            </div>

            <div class="stat-card">
                <h3>Cancelled Games</h3>
                <p>{{ $games->where('status', 'cancelled')->count() }}</p>
            </div>

        </div>

        {{-- Games Table --}}
        <div class="admin-card group-games-table-card">

            <div class="section-header">
                <h2>All Group Games</h2>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>Game</th>
                            <th>Booking</th>
                            <th>Players</th>
                            <th>Price / Player</th>
                            <th>Status</th>
                            <th>Actions</th>
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

                                {{-- Booking --}}
                                <td>
                                    #{{ $game->booking_id }}
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
                                    JOD
                                </td>

                                {{-- Status --}}
                                <td>
                                    <span class="status-badge
                                            {{ $game->status === 'open' ? 'badge-success' : '' }}
                                            {{ $game->status === 'full' ? 'badge-info' : '' }}
                                            {{ $game->status === 'cancelled' ? 'badge-danger' : '' }}
                                            {{ $game->status === 'completed' ? 'badge-warning' : '' }}">

                                        {{ ucfirst($game->status) }}

                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td>
                                    <div class="group-game-actions">

                                        <a href="{{ route('admin.group-games.edit', $game->id) }}"
                                            class="game-action-btn game-edit-btn">
                                            Edit
                                        </a>

                                        <form action="{{ route('admin.group-games.cancel', $game->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="game-action-btn game-cancel-btn">
                                                Cancel
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.group-games.destroy', $game->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="game-action-btn game-delete-btn">
                                                Delete
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6">
                                    <div class="table-empty">
                                        <h3>No Group Games Found</h3>
                                        <p>You haven't created any group games yet.</p>

                                        <a href="{{ route('admin.group-games.create') }}" class="admin-button">
                                            Create Your First Game
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
                {{ $games->links() }}
            </div>

        </div>

    </div>

@endsection
```