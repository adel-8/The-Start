@extends('layouts.app')

@section('title', __('Verify Email'))

@section('content')
<div class="container" style="max-width: 600px; margin: 4rem auto;">
    <div class="card" style="background: var(--color-surface); border-radius: 1rem; padding: 2rem; box-shadow: var(--shadow-sm); text-align: center;">
        <i class="fas fa-envelope" style="font-size: 3rem; color: var(--gold); margin-bottom: 1rem;"></i>
        <h2 style="margin-bottom: 1rem;">{{ __('Verify Your Email Address') }}</h2>
        <p style="margin-bottom: 1.5rem; color: var(--color-muted);">
            {{ __('Before proceeding, please check your email for a verification link.') }}
        </p>
        <p style="margin-bottom: 1.5rem; color: var(--color-muted);">
            {{ __('If you did not receive the email') }},
        </p>
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-primary" style="display: inline-block;">
                {{ __('Click here to request another') }}
            </button>
        </form>
        @if (session('message'))
            <div style="margin-top: 1rem; color: #10b981;">{{ session('message') }}</div>
        @endif
    </div>
</div>
@endsection