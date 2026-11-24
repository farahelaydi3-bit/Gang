@props(['interactive' => true])

@if ($interactive)
    <script src="{{ asset('assets/js/gang-theme.js') }}"></script>
@endif
<link rel="stylesheet" href="{{ asset('assets/css/gang-theme.css') }}">
