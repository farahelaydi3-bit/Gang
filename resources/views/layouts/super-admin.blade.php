<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Super Admin') | CourtBook</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@500;600;700;800&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/css/superadmin.css') }}">

    @stack('styles')
</head>

<body>

    <div class="owner-layout">

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay"
            id="sidebarOverlay"
            onclick="toggleSuperSidebar()">
        </div>

        <!-- Sidebar -->
        <aside class="owner-sidebar" id="superSidebar">

            <!-- Brand -->
            <a href="{{ route('super_admin.dashboard') }}"
                class="sidebar-brand">

                <div class="brand-icon">C</div>

                <div class="brand-text">
                    <h2>CourtBook</h2>
                    <span>Super Admin Panel</span>
                </div>
            </a>

            <div class="sidebar-label">Main Menu</div>

            <!-- Navigation -->
            <nav class="sidebar-nav"
                aria-label="Super Admin navigation">

                <!-- Overview -->
                <a href="{{ route('super_admin.dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('super_admin.dashboard') ? 'active' : '' }}">

                    <svg class="nav-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round">

                        <rect x="3" y="3" width="7" height="7" rx="1.5" />
                        <rect x="14" y="3" width="7" height="7" rx="1.5" />
                        <rect x="3" y="14" width="7" height="7" rx="1.5" />
                        <rect x="14" y="14" width="7" height="7" rx="1.5" />
                    </svg>

                    <span>Overview</span>
                </a>

                <!-- Owners -->
                <a href="{{ route('super_admin.admins.index') }}"
                    class="sidebar-link {{ request()->routeIs('super_admin.admins.*') ? 'active' : '' }}">

                    <svg class="nav-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round">

                        <circle cx="9" cy="8" r="3.5" />

                        <path d="M2.5 20c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6M16 5a3.5 3.5 0 0 1 0 6M18 14c2 .8 3.5 2.6 3.5 6" />
                    </svg>

                    <span>Manage Owners</span>
                </a>


                <a href="{{ route('super_admin.applications.index') }}"
                    class="sidebar-link {{ request()->routeIs('super_admin.applications.*') ? 'active' : '' }}">

                    <svg class="nav-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round">

                        <rect x="4" y="3" width="16" height="18" rx="2" />
                        <path d="M8 8h8M8 12h8M8 16h5" />
                    </svg>

                    <span>Owner Applications</span>
                </a>

                
                <!-- Players -->
                <a href="{{ route('super_admin.users.index') }}"
                    class="sidebar-link {{ request()->routeIs('super_admin.users.*') ? 'active' : '' }}">

                    <svg class="nav-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round">

                        <circle cx="12" cy="7.5" r="4" />

                        <path d="M4 21c0-4.3 3.3-7 8-7s8 2.7 8 7" />
                    </svg>

                    <span>Manage Players</span>
                </a>

                <!-- Sports -->
                @if (Route::has('super_admin.sports.index'))

                <a href="{{ route('super_admin.sports.index') }}"
                    class="sidebar-link {{ request()->routeIs('super_admin.sports.*') ? 'active' : '' }}">

                    <svg class="nav-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round">

                        <circle cx="12" cy="12" r="9" />
                        <path d="m8 5 4 3 4-3M6 12l4 2-1 5M18 12l-4 2 1 5" />
                    </svg>

                    <span>Manage Sports</span>
                </a>

                @endif

                <!-- Courts -->
                @if (Route::has('super_admin.courts.index'))

                <a href="{{ route('super_admin.courts.index') }}"
                    class="sidebar-link {{ request()->routeIs('super_admin.courts.*') ? 'active' : '' }}">

                    <svg class="nav-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round">

                        <rect x="3" y="3" width="18" height="18" rx="2" />
                        <path d="M3 12h18M12 3v18" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>

                    <span>Manage Courts</span>
                </a>

                @endif

                <!-- Analytics -->
                @if (Route::has('super_admin.analytics.index'))

                <a href="{{ route('super_admin.analytics.index') }}"
                    class="sidebar-link {{ request()->routeIs('super_admin.analytics.*') ? 'active' : '' }}">

                    <svg class="nav-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round">

                        <path d="M3 3v18h18" />
                        <path d="M7 16v-5M12 16V7M17 16v-8" />
                    </svg>

                    <span>Analytics</span>
                </a>

                @endif

            </nav>

            <!-- Sidebar Bottom -->
            <div class="sidebar-bottom">

                <!-- Logout -->
                <form method="POST"
                    action="{{ route('super_admin.logout') }}">

                    @csrf

                    <button type="submit"
                        class="sidebar-link sidebar-logout">

                        <svg class="nav-icon"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round">

                            <path d="M9 21H5.5A2.5 2.5 0 0 1 3 18.5v-13A2.5 2.5 0 0 1 5.5 3H9" />
                            <path d="m16 17 5-5-5-5M21 12H9" />
                        </svg>

                        <span>Logout</span>
                    </button>
                </form>

                <!-- Profile -->
                <div class="owner-profile">

                    <div class="profile-avatar">
                        {{ mb_strtoupper(mb_substr(auth('super_admin')->user()?->name ?? 'S', 0, 1)) }}
                    </div>

                    <div class="profile-info">

                        <strong>
                            {{ auth('super_admin')->user()?->name ?? 'Super Admin' }}
                        </strong>

                        <span>Super Admin</span>

                    </div>
                </div>

            </div>
        </aside>

        <!-- Main Content -->
        <main class="owner-main">

            <!-- Topbar -->
            <header class="owner-topbar">

                <div class="topbar-left">

                    <button class="topbar-button sidebar-toggle"
                        type="button"
                        aria-label="Toggle menu"
                        aria-controls="superSidebar"
                        aria-expanded="false"
                        onclick="toggleSuperSidebar()"
                        id="superSidebarButton">

                        <svg viewBox="0 0 24 24"
                            width="20"
                            height="20"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round">

                            <path d="M4 7h16M4 12h16M4 17h16" />
                        </svg>

                    </button>

                    <div class="topbar-title">

                        <h1>
                            @yield('page-title', 'Overview')
                        </h1>

                        <p>Manage your CourtBook platform</p>

                    </div>

                </div>

                <div class="topbar-actions">

                    <span style="font-weight:700;color:var(--admin-muted)">
                        Super Admin
                    </span>

                </div>

            </header>

            <!-- Page Content -->
            <section class="owner-content">
                @yield('content')
            </section>

        </main>
    </div>

    <!-- Flash Messages -->
    <div id="superAdminMessages"
        data-success="{{ session('success', '') }}"
        data-error="{{ session('error', '') }}"
        hidden>
    </div>

    <!-- Sidebar Script -->
    <script>
        function toggleSuperSidebar() {

            const sidebar = document.getElementById('superSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const button = document.getElementById('superSidebarButton');

            const opened = sidebar.classList.toggle('open');

            overlay.classList.toggle('show', opened);

            document.body.classList.toggle('no-scroll', opened);

            button.setAttribute(
                'aria-expanded',
                opened ? 'true' : 'false'
            );
        }

        document.addEventListener('keydown', function(event) {

            if (
                event.key === 'Escape' &&
                document.getElementById('superSidebar').classList.contains('open')
            ) {
                toggleSuperSidebar();
            }

        });
    </script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const messages = document.getElementById('superAdminMessages');

            if (!messages || !window.Swal) {
                return;
            }

            const success = messages.dataset.success;
            const error = messages.dataset.error;

            if (success) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: success,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }

            if (error) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: error,
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true
                });
            }

        });
    </script>

    @stack('scripts')

</body>

</html>