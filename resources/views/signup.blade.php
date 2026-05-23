@extends('layouts.auth')

@section('title', __('messages.signup_title') . ' · ' . ($settings['site_name'] ?? config('app.name', 'The Start')))

@push('styles')
    @vite('resources/css/signUp.css')
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
<div class="signup-container">
  <div class="signup-card">
    <div class="brand">
      @if(isset($settings['signup_brand_logo']) && $settings['signup_brand_logo'])
        <div class="brand-logo">
          <img src="{{ asset($settings['signup_brand_logo']) }}" alt="{{ __('messages.site_logo') }}" class="brand-logo-img">
        </div>
      @else
        <div class="brand-icon">✨</div>
      @endif
      <h1>{{ __('messages.create_account') }}</h1>
      <div class="welcome-sub">{{ __('messages.join_community') }}</div>
    </div>

    <form method="POST" action="{{ route('signup') }}">
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

      <div class="name-row">
        <div class="input-group">
          <label for="firstName">{{ __('messages.first_name') }}</label>
          <input type="text" id="firstName" name="firstName" class="input-field" placeholder="{{ __('messages.first_name_placeholder') }}" value="{{ old('firstName') }}" autocomplete="given-name">
        </div>
        <div class="input-group">
          <label for="lastName">{{ __('messages.last_name') }}</label>
          <input type="text" id="lastName" name="lastName" class="input-field" placeholder="{{ __('messages.last_name_placeholder') }}" value="{{ old('lastName') }}" autocomplete="family-name">
        </div>
      </div>

      <div class="input-group">
        <label for="email">{{ __('messages.email_address') }}</label>
        <input type="email" id="email" name="email" class="input-field" placeholder="{{ __('messages.email_placeholder') }}" value="{{ old('email') }}" autocomplete="email" required>
      </div>

      <div class="input-group">
        <label for="password">{{ __('messages.password') }}</label>
        <input type="password" id="password" name="password" class="input-field" placeholder="{{ __('messages.password_placeholder') }}" autocomplete="new-password" required>
        <div class="password-hint">
          <span>{{ __('messages.password_hint') }}</span>
          <span id="strengthIndicator" class="strength-badge">{{ __('messages.strength') }}</span>
        </div>
      </div>

      <div class="input-group">
        <label for="confirmPassword">{{ __('messages.confirm_password') }}</label>
        <input type="password" id="confirmPassword" name="password_confirmation" class="input-field" placeholder="{{ __('messages.confirm_password_placeholder') }}" autocomplete="off" required>
      </div>

      <div class="terms-group">
        <input type="checkbox" name="terms" id="termsCheckbox" required>
        <label for="termsCheckbox" class="terms-label">
          {!! __('messages.terms_agree', [
              'terms_link' => '<a href="'.route('terms').'">'.__('messages.terms_of_service').'</a>',
              'privacy_link' => '<a href="'.route('privacy').'">'.__('messages.privacy_policy').'</a>'
          ]) !!}
        </label>
      </div>

      <button type="submit" class="btn-signup">{{ __('messages.sign_up') }} →</button>
    </form>

    <div class="divider">
      <span>{{ __('messages.or_sign_up_with') }}</span>
    </div>

    <div class="social-buttons">
      @if(isset($settings['enable_google_login']) && $settings['enable_google_login'])
        <a href="{{ route('auth.google') }}" class="social-btn google-btn">
            <i class="fab fa-google"></i> {{ __('messages.continue_with_google') }}
        </a>
      @endif
      
      @if(isset($settings['enable_guest_checkout']) && $settings['enable_guest_checkout'])
        <a href="{{ route('home') }}" class="social-btn" id="signinRedirect">{{ __('messages.continue_as_guest') }}</a>
      @endif
    </div>

    <div class="signin-prompt">
      {!! __('messages.already_have_account', [
          'link' => '<a href="'.route('signin').'" class="signin-link" id="signinRedirect">'.__('messages.sign_in').'</a>'
      ]) !!}
    </div>
  </div>
</div>

<div id="demoToast" class="demo-toast">✨ {{ __('messages.join_dolphin') }}</div>
@endsection

@push('scripts')
    @vite('resources/js/signup.js')
@endpush