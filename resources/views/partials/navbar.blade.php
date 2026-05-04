@php
    // Fetch site name from settings (or fallback to app name or default)
    $siteName = \App\Models\Setting::where('setting_key', 'site_name')->value('setting_value') ?? config('app.name', 'the&Start');
@endphp

<nav class="navbar">
  <div class="navbar-container">
    <!-- Logo -->
    <a href="{{ route('home') }}" class="logo">{{ $siteName }}</a>

    <!-- Desktop Navigation Links -->
    <div class="nav-links">
      <a href="{{ route('home') }}">{{ __('messages.home') }}</a>
      <a href="{{ route('Shop') }}">{{ __('messages.shop') }}</a>
      <a href="{{ route('contact') }}">{{ __('messages.contact') }}</a>
      <a href="{{ route('about') }}">{{ __('messages.about') }}</a>
    </div>

    <!-- Right side icons -->
    <div class="nav-actions">
      <!-- Cart icon with badge -->
      <a href="{{ route('cart') }}" class="cart-link">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count">{{ cartCount() }}</span>
      </a>

      @auth
        <!-- User avatar / icon -->
        <div class="user-dropdown-wrapper">
          <button class="user-avatar" id="userMenuBtn">
            <i class="fas fa-user-circle"></i>
          </button>
          <div class="user-dropdown" id="userDropdown">
            <div class="dropdown-header">
              {{ auth()->user()->username }}
            </div>
            <div class="dropdown-divider"></div>
            <a href="{{ route('orders.index') }}" class="dropdown-item">
              <i class="fas fa-box"></i> {{ __('messages.my_orders') }}
            </a>
            <a href="{{ route('addresses.index') }}" class="dropdown-item">
                <i class="fas fa-map-marker-alt"></i> {{ __('messages.my_addresses') }}
            </a>
            <a href="{{ route('profile.show') }}" class="dropdown-item">
              <i class="fas fa-user"></i> {{ __('messages.profile') }}
            </a>
            <div class="language-switcher">
                <a href="{{ route('locale', 'en') }}" class="{{ app()->getLocale() == 'en' ? 'active' : '' }}">EN</a>
                <span>|</span>
                <a href="{{ route('locale', 'ar') }}" class="{{ app()->getLocale() == 'ar' ? 'active' : '' }}">AR</a>
            </div>

            @if(auth()->check() && in_array(auth()->user()->role_id, [1, 2]))
                <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                    <i class="fas fa-tachometer-alt"></i> {{ __('messages.admin_panel') }}
                </a>
            @endif

            <div class="dropdown-divider"></div>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="dropdown-item logout-btn">
                <i class="fas fa-sign-out-alt"></i> {{ __('messages.logout') }}
              </button>
            </form>
          </div>
        </div>
      @else
        <a href="{{ route('signin') }}" class="btn">{{ __('messages.login') }}</a>
        <a href="{{ route('signup') }}" class="btn">{{ __('messages.register') }}</a>
      @endauth
    </div>

    <!-- Mobile menu toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
      <i class="fas fa-bars"></i>
    </button>
  </div>

  <!-- Mobile menu -->
  <div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-header">
      <span class="mobile-logo">{{ $siteName }}</span>
      <button class="close-menu" id="closeMenuBtn"><i class="fas fa-times"></i></button>
    </div>
    <div class="mobile-links">
      <a href="{{ route('home') }}">{{ __('messages.home') }}</a>
      <a href="{{ route('Shop') }}">{{ __('messages.shop') }}</a>
      <a href="{{ route('contact') }}">{{ __('messages.contact') }}</a>
      <a href="{{ route('about') }}">{{ __('messages.about') }}</a>
      <div class="mobile-divider"></div>
      @auth
        <div class="mobile-user-info">
          <span class="mobile-user-name">{{ auth()->user()->username }}</span>
          <a href="{{ route('orders.index') }}" class="mobile-link">
            <i class="fas fa-box"></i> {{ __('messages.my_orders') }}
          </a>
          <a href="{{ route('addresses.index') }}" class="mobile-link">
              <i class="fas fa-map-marker-alt"></i> {{ __('messages.my_addresses') }}
          </a>
          <a href="{{ route('profile.show') }}" class="mobile-link">
            <i class="fas fa-user"></i> {{ __('messages.profile') }}
          </a>

          @if(auth()->check() && in_array(auth()->user()->role_id, [1, 2]))
            <a href="{{ route('admin.dashboard') }}" class="mobile-link">
                <i class="fas fa-tachometer-alt"></i> {{ __('messages.admin_panel') }}
            </a>
          @endif

          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="mobile-link logout-btn-mobile">
              <i class="fas fa-sign-out-alt"></i> {{ __('messages.logout') }}
            </button>
          </form>
        </div>
      @else
        <a href="{{ route('signin') }}" class="mobile-link">{{ __('messages.login') }}</a>
        <a href="{{ route('signup') }}" class="mobile-link">{{ __('messages.register') }}</a>
      @endauth
    </div>
  </div>
</nav>

@push('scripts')
  @vite('resources/js/navbar.js')
@endpush