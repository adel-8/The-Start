@extends('layouts.auth')

@section('title', __('messages.signin_title') . ' · ' . ($settings['site_name'] ?? config('app.name', 'The Start')))

@push('styles')
    @vite('resources/css/signin.css')
    <style>
        /* Force fixed logo size */
        .brand-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 50%;
            background: #f5f5f5;
        }
        .brand-logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
    </style>
@endpush

@section('content')
<div class="signin-container">
  <div class="signin-card">
    <div class="brand">
      @if(isset($settings['signin_brand_logo']) && $settings['signin_brand_logo'])
        <div class="brand-logo">
          <img src="{{ asset($settings['signin_brand_logo']) }}" alt="{{ __('messages.site_logo') }}" class="brand-logo-img">
        </div>
      @elseif(isset($settings['signup_brand_logo']) && $settings['signup_brand_logo'])
        {{-- Fallback to signup logo if signin logo not set --}}
        <div class="brand-logo">
          <img src="{{ asset($settings['signup_brand_logo']) }}" alt="{{ __('messages.site_logo') }}" class="brand-logo-img">
        </div>
      @else
        <div class="brand-icon">✦</div>
      @endif
      <h1>{{ __('messages.welcome_back') }}</h1>
      <div class="welcome-sub">{{ __('messages.signin_subtitle') }}</div>
    </div>

    <form method="POST" action="{{ route('signin') }}">
      @csrf

      @if ($errors->any())
        <div class="error-messages">
            <ul>
                @foreach ($errors->all() as $error)
                    <li style="color: red;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
      @endif

      <div class="input-group">
        <label for="email">{{ __('messages.email_address') }}</label>
        <input type="email" id="email" name="email" class="input-field" placeholder="{{ __('messages.email_placeholder') }}" value="{{ old('email') }}" autocomplete="email" required>
      </div>
      <div class="input-group">
        <label for="password">{{ __('messages.password') }}</label>
        <div class="password-wrapper">
            <input type="password" id="password" name="password" class="input-field" placeholder="{{ __('messages.password_placeholder_signin') }}" autocomplete="current-password" required>
            <button type="button" class="toggle-password" aria-label="{{ __('messages.show_password') }}">
                <i class="fa-regular fa-eye-slash"></i>
            </button>
        </div>
      </div>

      <div class="form-options">
        <label class="checkbox">
          <input type="checkbox" id="rememberMe" name="remember"> {{ __('messages.remember_me') }}
        </label>
        <a href="{{ route('password.request') }}" class="forgot-link">{{ __('messages.forgot_password') }}</a>
      </div>

      <button type="submit" class="btn-signin">{{ __('messages.sign_in') }} →</button>
    </form>

    <div class="divider">
      <span>{{ __('messages.or_continue_with') }}</span>
    </div>

    <div class="social-buttons">
      @if(isset($settings['enable_google_login']) && $settings['enable_google_login'])
        <a href="{{ route('auth.google') }}" class="social-btn google-btn">
            <i class="fab fa-google"></i> {{ __('messages.continue_with_google') }}
        </a>
      @endif
      <!-- GitHub login removed as requested -->
    </div>

    <div class="signup-prompt">
      {!! __('messages.no_account_yet', ['link' => '<a href="'.route('signup').'" class="signup-link" id="signupRedirect">'.__('messages.create_account').'</a>']) !!}
    </div>
  </div>
</div>

<div id="demoToast" class="demo-toast">✨ {{ __('messages.dolphin_access') }}</div>
@endsection

@push('scripts')
    @vite('resources/js/signin.js')
@endpush