
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Details - CourtBook</title>
</head>
<body>

    <h1>Review Details</h1>

    @if (session('success'))
        <p style="color: green;">
            {{ session('success') }}
        </p>
    @endif

    <p>Review ID: {{ $review->id }}</p>

    <p>
        User: {{ $review->user->name ?? 'Unknown User' }}
    </p>

    <p>
        Court: {{ $review->court->name ?? 'Unknown Court' }}
    </p>

    <p>
        Rating: {{ $review->rating }} / 5
    </p>

    <p>
        Comment: {{ $review->comment ?? 'No comment' }}
    </p>

    @auth
        @if ($review->user_id === auth()->id())

            <a href="{{ route('reviews.edit', $review) }}">
                Edit Review
            </a>

            <br><br>

            <form action="{{ route('reviews.destroy', $review) }}"
                  method="POST">
                @csrf
                @method('DELETE')

                <button type="submit">
                    Delete Review
                </button>
            </form>

        @endif
    @endauth

    <br>

    <a href="{{ route('reviews.index') }}">
        Back to Reviews
    </a>

</body>
</html>
