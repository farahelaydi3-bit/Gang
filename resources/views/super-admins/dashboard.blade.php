<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard | CourtBook</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h1>Super Admin Dashboard</h1>
                <p class="text-muted">
                    Welcome,
                    {{ Auth::guard('super_admin')->user()->name }}
                </p>
            </div>

            <form method="POST" action="{{ route('super_admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger">
                    Logout
                </button>
            </form>

        </div>

        <div class="row g-4">

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Total Players</h6>
                        <h2>{{ $totalPlayers }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Total Owners</h6>
                        <h2>{{ $totalOwners }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Pending Owners</h6>
                        <h2>{{ $pendingOwners }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Active Courts</h6>
                        <h2>{{ $activeCourts }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Public Games</h6>
                        <h2>{{ $publicGames }}</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6>Total Bookings</h6>
                        <h2>{{ $totalBookings }}</h2>
                    </div>
                </div>
            </div>

        </div>

    </div>

</body>

</html>