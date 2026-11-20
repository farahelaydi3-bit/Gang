
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Review - CourtBook</title>
</head>
<body>

    <h1>Edit Review</h1>

    <p>Review #{{ $review->id }}</p>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li style="color: red;">{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('reviews.update', $review) }}" method="POST">
        @csrf
        @method('PUT')

        <label for="rating">Rating:</label>

        <select name="rating" id="rating" required>
            @for ($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}"
                    @selected(old('rating', $review->rating) == $i)>
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
        >{{ old('comment', $review->comment) }}</textarea>

        <br><br>

        <button type="submit">Save Changes</button>
    </form>

    <br>

    <a href="{{ route('reviews.show', $review) }}">
        Cancel
    </a>

</body>
</html>
