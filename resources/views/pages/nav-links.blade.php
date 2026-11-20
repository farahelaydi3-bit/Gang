@foreach ($links as $name => $label)
@if (Route::has('courtbook.' . $name))
<a
    href="{{ route('courtbook.' . $name) }}"
    class="{{ request()->routeIs('courtbook.' . $name) ? 'active' : '' }}">
    {{ __($label) }}
</a>
@endif
@endforeach