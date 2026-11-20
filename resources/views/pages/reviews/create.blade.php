
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Review - CourtBook</title>
</head>
<body>

    <h1>Write a Review</h1>

    <p>Booking #{{ $booking->id }}</p>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li style="color: red;">{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('reviews.store') }}" method="POST">
        @csrf

        <input type="hidden"
               name="booking_id"
               value="{{ $booking->id }}">

        <label for="rating">Rating:</label>

        <select name="rating" id="rating" required>
            <option value="">Choose rating</option>

            @for ($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}"
                    @selected(old('rating') == $i)>
                    {{ $i }} Star(s)
                </option>
            @endfor
        </select>

        <br><br>

        <label for="comment">Comment (optional):</label>
        <br>

        <textarea
            name="comment"
            id="comment"
            rows="5"
            cols="40"
            maxlength="2000"
        >{{ old('comment') }}</textarea>

        <br><br>

        <button type="submit">Submit Review</button>
    </form>

    <br>

    <a href="{{ route('reviews.index') }}">
        Back to Reviews
    </a>

</body>
</html>
