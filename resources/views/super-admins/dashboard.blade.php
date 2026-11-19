<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Super Admin Dashboard</title>
</head>
<body>
    <h1>Super Admin Dashboard</h1>

    <p>
        Welcome,
        {{ Auth::guard('super_admin')->user()->name }}
    </p>

    <form method="POST" action="{{ route('super_admin.logout') }}">
        @csrf

        <button type="submit">Logout</button>
    </form>
</body>
</html>