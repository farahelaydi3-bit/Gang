<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Account Status - CourtBook</title>
</head>

<body>
    <h1>Account Status</h1>

    <h2>Hello, {{ Auth::guard('admin')->user()->name }}</h2>

    @if (Auth::guard('admin')->user()->status === 'pending')
    <p>Your account is pending Super Admin approval.</p>
    @elseif (Auth::guard('admin')->user()->status === 'rejected')
    <p>Your owner application has been rejected.</p>
    @else
    <p>Your account status has changed.</p>
    @endif

    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>

</html>