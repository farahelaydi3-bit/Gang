<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Players | CourtBook</title>

    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container py-5">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h2 class="fw-bold">Players Management</h2>
                <p class="text-muted mb-0">
                    View and manage all registered CourtBook players.
                </p>
            </div>

            <a href="{{ route('super_admin.dashboard') }}"
                class="btn btn-outline-secondary">
                Back to Dashboard
            </a>

        </div>

        <!-- Success Message -->
        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <!-- Validation Errors -->
        @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
        @endif

        <!-- Players Table -->
        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    All Players ({{ $users->count() }})
                </h5>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">
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

                            @forelse($users as $user)

                            <tr>
                                <td>{{ $user->id }}</td>

                                <td class="fw-semibold">
                                    {{ $user->name }}
                                </td>

                                <td>{{ $user->email }}</td>

                                <td>{{ $user->phone ?? '-' }}</td>

                                <!-- Status -->
                                <td>
                                    @if($user->status === 'active')
                                    <span class="badge bg-success">
                                        Active
                                    </span>
                                    @elseif($user->status === 'suspended')
                                    <span class="badge bg-danger">
                                        Suspended
                                    </span>
                                    @else
                                    <span class="badge bg-secondary">
                                        {{ ucfirst($user->status ?? 'Unknown') }}
                                    </span>
                                    @endif
                                </td>

                                <!-- Registration Date -->
                                <td>
                                    {{ $user->created_at?->format('Y-m-d') ?? '-' }}
                                </td>

                                <!-- Update Status -->
                                <td>
                                    <form method="POST"
                                        action="{{ route('super_admin.users.status', $user->id) }}">

                                        @csrf
                                        @method('PATCH')

                                        <div class="d-flex gap-2">

                                            <select name="status"
                                                class="form-select form-select-sm">

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
                                                class="btn btn-success btn-sm">
                                                Save
                                            </button>

                                        </div>

                                    </form>
                                </td>
                            </tr>

                            @empty

                            <tr>
                                <td colspan="7"
                                    class="text-center text-muted py-5">
                                    No players registered yet.
                                </td>
                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</body>

</html>