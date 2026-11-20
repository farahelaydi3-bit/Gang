<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Court Owners | CourtBook</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container py-5">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Court Owners Management</h2>
                <p class="text-muted mb-0">
                    Manage court owner accounts and approval status.
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

        <!-- Owners Table -->
        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white py-3">
                <h5 class="mb-0">All Court Owners ({{ $admins->count() }})</h5>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">
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

                            @forelse($admins as $admin)

                            <tr>
                                <td>{{ $admin->id }}</td>

                                <td class="fw-semibold">
                                    {{ $admin->name }}
                                </td>

                                <td>{{ $admin->business_name }}</td>

                                <td>{{ $admin->email }}</td>

                                <td>{{ $admin->phone }}</td>

                                <!-- Account Status -->
                                <td>
                                    @if($admin->status === 'active')
                                    <span class="badge bg-success">
                                        Active
                                    </span>

                                    @elseif($admin->status === 'pending')
                                    <span class="badge bg-warning text-dark">
                                        Pending
                                    </span>

                                    @elseif($admin->status === 'rejected')
                                    <span class="badge bg-danger">
                                        Rejected
                                    </span>

                                    @elseif($admin->status === 'suspended')
                                    <span class="badge bg-secondary">
                                        Suspended
                                    </span>

                                    @else
                                    <span class="badge bg-dark">
                                        Unknown
                                    </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $admin->created_at?->format('Y-m-d') ?? '-' }}
                                </td>

                                <!-- Update Status -->
                                <td>
                                    <form method="POST"
                                        action="{{ route('super_admin.admins.status', $admin->id) }}">

                                        @csrf
                                        @method('PATCH')

                                        <div class="d-flex gap-2">

                                            <select name="status"
                                                class="form-select form-select-sm">

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
                                                class="btn btn-success btn-sm">
                                                Save
                                            </button>

                                        </div>

                                    </form>
                                </td>
                            </tr>

                            @empty

                            <tr>
                                <td colspan="8"
                                    class="text-center text-muted py-5">
                                    No court owners registered yet.
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