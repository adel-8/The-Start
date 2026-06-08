<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-currency-symbol="{{ __('admin.currency_symbol') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel - @yield('title')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700&display=swap" rel="stylesheet" crossorigin="anonymous">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @vite('resources/css/admin.css')
    @stack('styles')
</head>
<body dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <h2>{{ __('admin.admin_panel') }}</h2>
                <button class="close-sidebar" id="closeSidebarBtn"><i class="fas fa-times"></i></button>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i> {{ __('admin.dashboard') }}
                    </a></li>
                    <li><a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart"></i> {{ __('admin.orders') }}
                    </a></li>
                    <li><a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <i class="fas fa-box"></i> {{ __('admin.products') }}
                    </a></li>
                    <li><a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i> {{ __('admin.categories') }}
                    </a></li>
                    <li><a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                        <i class="fas fa-star"></i> {{ __('admin.reviews') }}
                    </a></li>
                    @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 2)
                        <li><a href="{{ route('admin.contact-messages.index') }}" class="{{ request()->routeIs('admin.contact-messages.*') ? 'active' : '' }}">
                            <i class="fas fa-envelope"></i> {{ __('admin.contact_messages') }}
                        </a></li>
                    @endif
                    @if(auth()->user()->role_id == 1)
                        <li><a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="fas fa-users"></i> {{ __('admin.customers') }}
                        </a></li>
                    @endif
                    <li><a href="{{ route('admin.coupons.index') }}" class="{{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
                        <i class="fas fa-percent"></i> {{ __('admin.coupons') }}
                    </a></li>
                    <li><a href="{{ route('admin.banners.index') }}" class="{{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                        <i class="fas fa-image"></i> {{ __('admin.banners') }}
                    </a></li>
                    @if(auth()->user()->role_id == 1)
                        <li><a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <i class="fas fa-cog"></i> {{ __('admin.settings') }}
                        </a></li>
                        <li><a href="{{ route('admin.analytics') }}" class="{{ request()->routeIs('admin.analytics') ? 'active' : '' }}">
                            <i class="fas fa-chart-line"></i> {{ __('admin.analytics') }}
                        </a></li>
                    @endif
                </ul>
            </nav>
        </aside>

        <div class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="fas fa-bars"></i></button>
                    <h1>@yield('title', __('admin.dashboard'))</h1>
                </div>
                <div class="admin-user">
                    <span class="user-name">{{ auth()->user()->name ?? auth()->user()->username }}</span>
                </div>
            </header>

            <main class="admin-content">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Dropdown moved outside main wrapper -->
    <div class="user-dropdown" id="userDropdown">
        <div class="dropdown-header">{{ auth()->user()->name ?? auth()->user()->username }}</div>
        <a href="{{ route('home') }}" target="_blank"><i class="fas fa-globe"></i> {{ __('admin.visit_store') }}</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"><i class="fas fa-sign-out-alt"></i> {{ __('admin.logout') }}</button>
        </form>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    @stack('scripts')
    <script>
    // Mobile sidebar toggle
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const closeBtn = document.getElementById('closeSidebarBtn');

    function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
    }
    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    }

    if (mobileToggle) mobileToggle.addEventListener('click', openSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // Dropdown positioning – works for both LTR and RTL
    const userName = document.querySelector('.admin-user');
    const userDropdown = document.getElementById('userDropdown');

    function positionDropdown() {
        if (!userName || !userDropdown) return;
        const rect = userName.getBoundingClientRect();
        userDropdown.style.position = 'fixed';
        userDropdown.style.top = (rect.bottom + 5) + 'px';
        
        // Detect RTL by checking body dir attribute
        const isRtl = document.body.dir === 'rtl';
        
        if (isRtl) {
            // In RTL, align dropdown to the left edge of the user element
            userDropdown.style.left = rect.left + 'px';
            userDropdown.style.right = 'auto';
        } else {
            // In LTR, align dropdown to the right edge (original behavior)
            userDropdown.style.right = (window.innerWidth - rect.right) + 'px';
            userDropdown.style.left = 'auto';
        }
    }

    if (userName && userDropdown) {
        userName.addEventListener('click', function(e) {
            e.stopPropagation();
            positionDropdown();
            userDropdown.classList.toggle('show');
        });
        document.addEventListener('click', function(e) {
            if (!userName.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('show');
            }
        });
        window.addEventListener('resize', function() {
            if (userDropdown.classList.contains('show')) {
                positionDropdown();
            }
        });
    }
</script>
</body>
</html>