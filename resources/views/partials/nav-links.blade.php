@foreach ($links as $name => $label)
    <a href="{{ route('courtbook.'.$name) }}" @if ($activePage === $name) aria-current="page" @endif>{{ __($label) }}</a>
@endforeach

