<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Owner Dashboard') | CourtBook</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/admins.css') }}">

    @stack('styles')
</head>

<body>

    <div class="owner-layout">

        {{-- Mobile overlay --}}
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleOwnerSidebar()"></div>

        {{-- Sidebar --}}
        <aside class="owner-sidebar" id="ownerSidebar">

            <a href="{{ url('/admin/dashboard') }}" class="sidebar-brand">
                <div class="brand-icon">C</div>

                <div class="brand-text">
                    <h2>CourtBook</h2>
                    <span>Owner Dashboard</span>
                </div>
            </a>

            <div class="sidebar-label">Main Menu</div>

            <nav class="sidebar-nav">

                <a href="{{ url('/admin/dashboard') }}"
                    class="sidebar-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                    <span>Overview</span>
                </a>

                <a href="{{ url('/admin/courts') }}"
                    class="sidebar-link {{ request()->is('admin/courts*') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="M12 5v14"/><circle cx="12" cy="12" r="2.6"/></svg>
                    <span>My Courts</span>
                </a>

                <a href="{{ route('admin.site-studio.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.site-studio.*') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M9 9v11M13 14h4"/></svg>
                    <span>Gang Site Studio</span>
                </a>

                <a href="{{ url('/admin/reservations') }}"
                    class="sidebar-link {{ request()->is('admin/reservations*') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4.5" width="18" height="16" rx="2.5"/><path d="M3 9.5h18M8 2.5v4M16 2.5v4"/><path d="m9 15 2 2 4-4"/></svg>
                    <span>Reservations</span>
                </a>
                <a href="{{ url('/admin/group-games') }}"
                    class="sidebar-link {{ request()->is('admin/public-games*') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><path d="M16 4.7a3.5 3.5 0 0 1 0 6.6M18 14.3c2 .8 3.5 2.6 3.5 5.7"/></svg>
                    <span>Group  Games</span>
                </a> 
                <a href="{{ url('/admin/subscriptions') }}"
                    class="sidebar-link {{ request()->is('admin/subscription*') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 10h19M6.5 15h4"/></svg>
                    <span>Subscription</span>
                </a>

            </nav>

            <div class="sidebar-bottom">

                <a href="{{ url('/admin/profile') }}"
                    class="sidebar-link {{ request()->is('admin/profile*') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/></svg>
                    <span>My Profile</span>
                </a>

                <form method="POST" action="{{ url('/admin/logout') }}">
                    @csrf

                    <button type="submit" class="sidebar-link sidebar-logout">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5.5A2.5 2.5 0 0 1 3 18.5v-13A2.5 2.5 0 0 1 5.5 3H9"/><path d="m16 17 5-5-5-5M21 12H9"/></svg>
                        <span>Logout</span>
                    </button>
                </form>

                <div class="owner-profile">
                    <div class="profile-avatar">
                        {{ strtoupper(substr(auth('admin')->user()->name ?? 'O', 0, 1)) }}
                    </div>

                    <div class="profile-info">
                        <strong>{{ auth('admin')->user()->name ?? 'Court Owner' }}</strong>
                        <span>Court Owner</span>
                    </div>
                </div>

            </div>

        </aside>

        {{-- Main Content --}}
        <main class="owner-main">

            <header class="owner-topbar">

                <div class="topbar-left">

                    <button class="topbar-button sidebar-toggle" type="button" aria-label="Toggle menu"
                        onclick="toggleOwnerSidebar()">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                    </button>

                    <div class="topbar-title">
                        <h1>@yield('page-title', 'Overview')</h1>
                        <p>Manage your courts and bookings</p>
                    </div>

                </div>

                <div class="topbar-actions">
                    <button class="topbar-button" type="button" title="Notifications" aria-label="Notifications">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9a6 6 0 1 1 12 0c0 6 2.5 7.5 2.5 7.5h-17S6 15 6 9Z"/><path d="M10 20a2 2 0 0 0 4 0"/></svg>
                    </button>
                </div>

            </header>

            <section class="owner-content">
                @yield('content')
            </section>

        </main>

    </div>

    <script>
        function toggleOwnerSidebar() {
            document.getElementById('ownerSidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show');
            document.body.classList.toggle('no-scroll');
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && document.getElementById('ownerSidebar').classList.contains('open')) {
                toggleOwnerSidebar();
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if (session('success'))
    <script>
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            iconColor: '#5a7a4c',
            background: '#fffcf5',
            color: '#2a2320',
            title: @json(session('success')),
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    </script>
@endif

@if (session('error'))
    <script>
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            iconColor: '#b4483b',
            background: '#fffcf5',
            color: '#2a2320',
            title: @json(session('error')),
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    </script>
@endif

    @stack('scripts')

</body>
</html>