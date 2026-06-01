@extends('layouts.auth')

@section('title', __('messages.verify_email_title') . ' · ' . ($settings['site_name'] ?? config('app.name', 'The Start')))

@push('styles')
    @vite('resources/css/signUp.css')
@endpush

@section('content')
<div class="signup-container">
  <div class="signup-card">
    <div class="brand">
      <div class="brand-icon">📧</div>
      <h1>{{ __('messages.verify_email_heading') }}</h1>
      <div class="welcome-sub">{{ __('messages.verify_email_instructions') }}</div>
    </div>

    @if(session('success'))
      <div class="success-messages" style="margin-bottom:1rem;color:green;">
        {{ session('success') }}
      </div>
    @endif

    <div class="input-group">
      <p>{{ __('messages.verify_email_message') }}</p>
    </div>

    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit" class="btn-signup">{{ __('messages.resend_verification_link') }}</button>
    </form>

    <div class="signin-prompt" style="margin-top:1rem;">
      {!! __('messages.already_have_account', [
          'link' => '<a href="'.route('signin').'" class="signin-link">'.__('messages.sign_in').'</a>'
      ]) !!}
    </div>
  </div>
</div>
@endsection
