
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriptions - CourtBook</title>
</head>
<body>
    <h1>Subscription Management</h1>

    <h2>Available Subscription Plans</h2>

    @foreach ($durations as $duration)
        <div>
            <p>المدة: {{ $duration['days'] }} أيام</p>
            <p>الخصم: {{ $duration['discount'] }}%</p>
            <hr>
        </div>
    @endforeach

    <p>Subscription page is working!</p>
</body>
</html>
