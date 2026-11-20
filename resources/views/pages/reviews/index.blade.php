
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CourtBook - Reviews</title>
</head>
@auth
    <hr>

    <h3>DEV4 - Authorization Test</h3>

    <form action="{{ route('reviews.update', 1) }}" method="POST">
        @csrf
        @method('PATCH')

        <input type="hidden" name="rating" value="1">
        <input type="hidden" name="comment" value="Unauthorized update test">

        <button type="submit">
            Test Unauthorized Update
        </button>
    </form>
@endauth
<body>

    <h1>Customer Reviews</h1>

    @auth
        <p>
            To add a review, open a completed booking
            and select "Write Review".
        </p>
    @endauth

    @forelse ($reviews as $review)
        <hr>

        <h3>{{ $review->user->name ?? 'User' }}</h3>

        <p>
            Court: {{ $review->court->name ?? 'Unknown Court' }}
        </p>

        <p>
            Rating: {{ $review->rating }} / 5
        </p>

        <p>
            Comment: {{ $review->comment ?? 'No comment' }}
        </p>

        <a href="{{ route('reviews.show', $review) }}">
            View Review
        </a>

        @auth
            @if ($review->user_id === auth()->id())
                <a href="{{ route('reviews.edit', $review) }}">
                    Edit
                </a>

                <form action="{{ route('reviews.destroy', $review) }}"
                      method="POST">
                    @csrf
                    @method('DELETE')

                    <button type="submit">
                        Delete
                    </button>
                </form>
            @endif
        @endauth

    @empty
        <p>No reviews available yet.</p>
    @endforelse

    {{ $reviews->links() }}

</body>
</html>
