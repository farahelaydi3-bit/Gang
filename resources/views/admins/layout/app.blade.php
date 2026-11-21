<!DOCTYPE html>
<html lang="@yield('html-lang', str_replace('_', '-', app()->getLocale()))">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Owner Dashboard') | Gang</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/admins.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/superadmin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/super-admin-pages.css') }}">

    @stack('styles')
</head>

<body>

    <div class="owner-layout dashboard-layout">

        {{-- Mobile overlay --}}
        <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleOwnerSidebar()"></div>

        {{-- Sidebar --}}
        <aside class="owner-sidebar" id="ownerSidebar">

            <a href="{{ url('/admin/dashboard') }}" class="sidebar-brand">
                <img class="brand-logo" src="{{ asset('courtbook-assets/images/gang-logo.png') }}" alt="Gang logo">

                <div class="brand-text">
                    <h2>Gang</h2>
                    <span>Owner Panel</span>
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
                    class="sidebar-link {{ request()->is('admin/group-games*') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6"/><path d="M16 4.7a3.5 3.5 0 0 1 0 6.6M18 14.3c2 .8 3.5 2.6 3.5 5.7"/></svg>
                    <span>Group  Games</span>
                </a> 
                <a href="{{ route('admin.sms-logs.index') }}"
                    class="sidebar-link {{ request()->routeIs('admin.sms-logs.*') ? 'active' : '' }}">
                    <span>SMS History</span>
                </a>
                <a href="{{ url('/admin/subscriptions') }}"
                    class="sidebar-link {{ request()->is('admin/subscription*') ? 'active' : '' }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="5" width="19" height="14" rx="2.5"/><path d="M2.5 10h19M6.5 15h4"/></svg>
                    <span>Subscription</span>
                </a>

            </nav>

            <div class="sidebar-bottom">

                <form method="POST" action="{{ url('/admin/logout') }}">
                    @csrf

                    <button type="submit" class="sidebar-link sidebar-logout">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5.5A2.5 2.5 0 0 1 3 18.5v-13A2.5 2.5 0 0 1 5.5 3H9"/><path d="m16 17 5-5-5-5M21 12H9"/></svg>
                        <span>Logout</span>
                    </button>
                </form>

                <a href="{{ route('admin.profile.index') }}"
                    class="owner-profile {{ request()->is('admin/profile*') ? 'active' : '' }}"
                    aria-label="Open your profile">
                    <div class="profile-avatar">
                        {{ strtoupper(substr(auth('admin')->user()->name ?? 'O', 0, 1)) }}
                    </div>
                    <div class="profile-info">
                        <strong>{{ auth('admin')->user()->name ?? 'Court Owner' }}</strong>
                        <span>Court Owner</span>
                    </div>
                </a>

            </div>

        </aside>

        {{-- Main Content --}}
        <main class="owner-main">

            <button class="owner-mobile-menu" type="button" aria-label="Open navigation menu"
                aria-controls="ownerSidebar" aria-expanded="false" onclick="toggleOwnerSidebar()">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>

            <section class="owner-content @yield('page-class')">
                <x-dashboard-notifications />
                @yield('content')
            </section>

        </main>

    </div>

    <script>
        function toggleOwnerSidebar() {
            const sidebar = document.getElementById('ownerSidebar');
            const isOpen = sidebar.classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show', isOpen);
            document.body.classList.toggle('no-scroll', isOpen);
            document.querySelector('.owner-mobile-menu')?.setAttribute('aria-expanded', String(isOpen));
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && document.getElementById('ownerSidebar').classList.contains('open')) {
                toggleOwnerSidebar();
            }
        });
    </script>
    @stack('scripts')
    @yield('scripts')

</body>
</html>
