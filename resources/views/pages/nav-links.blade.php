@foreach ($links as $name => $label)
@if (Route::has('courtbook.' . $name))
<a
    href="{{ route('courtbook.' . $name) }}"
    @if ($activePage===$name) aria-current="page" @endif>
    {{ __($label) }}
</a>
@endif
@endforeach