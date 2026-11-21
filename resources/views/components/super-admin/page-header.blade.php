@props(['title', 'description' => null, 'reference' => null])

<header {{ $attributes->class(['overview-welcome', 'super-admin-page-header']) }} aria-labelledby="super-admin-page-title">
    <div class="overview-welcome-copy">
        @if ($reference)
            <p class="super-admin-page-reference">{{ $reference }}</p>
        @endif
        <h1 id="super-admin-page-title" dir="auto">{{ $title }}</h1>
        @if ($description)
            <p>{{ $description }}</p>
        @endif
        {{ $slot }}
    </div>

    @if (isset($actions))
        <div class="super-admin-page-actions">{{ $actions }}</div>
    @endif

    <span class="overview-orbit overview-orbit-one" aria-hidden="true"></span>
    <span class="overview-orbit overview-orbit-two" aria-hidden="true"></span>
</header>
