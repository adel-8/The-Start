@extends('layouts.auth')

@section('title', __('messages.signin_title') . ' · ' . ($settings['site_name'] ?? config('app.name', 'The Start')))

@push('styles')
    @vite('resources/css/signin.css')
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

      @if(isset($settings['enable_github_login']) && $settings['enable_github_login'] && config('services.github.client_id'))
        <a href="{{ route('auth.github') }}" class="social-btn github-btn">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.464-1.11-1.464-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.03-2.682-.103-.253-.447-1.27.098-2.646 0 0 .84-.269 2.75 1.025.8-.223 1.65-.334 2.5-.334.85 0 1.7.111 2.5.334 1.91-1.294 2.75-1.025 2.75-1.025.545 1.376.201 2.393.099 2.646.64.698 1.03 1.591 1.03 2.682 0 3.841-2.337 4.687-4.565 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.161 22 16.418 22 12c0-5.523-4.477-10-10-10z" />
          </svg>
          {{ __('messages.github') }}
        </a>
      @endif
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