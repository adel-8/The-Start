@extends('layouts.app')

@section('title', 'Reset Password')

@push('styles')
<style>
    .reset-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 60vh;
        padding: 2rem 1rem;
    }
    .reset-card {
        max-width: 480px;
        width: 100%;
        background: var(--color-surface);
        border-radius: 1.5rem;
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-md);
        padding: 2rem;
    }
    .reset-card h1 {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--color-primary);
        text-align: center;
    }
    .reset-card p {
        text-align: center;
        color: var(--color-muted);
        margin-bottom: 1.5rem;
    }
    .form-group {
        margin-bottom: 1.2rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--color-text);
    }
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--color-border);
        border-radius: 0.75rem;
        background: white;
        transition: 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(100, 95, 125, 0.1);
    }
    .btn-primary {
        width: 100%;
        background: var(--color-primary);
        color: white;
        border: none;
        padding: 0.75rem;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: 0.2s;
    }
    .btn-primary:hover {
        background: var(--color-primary-hover);
        transform: translateY(-2px);
    }
    .alert-success {
        background: #d4edda;
        color: #155724;
        padding: 0.75rem;
        border-radius: 0.75rem;
        margin-bottom: 1rem;
        border: 1px solid #c3e6cb;
    }
    .invalid-feedback {
        color: #c72a2a;
        font-size: 0.8rem;
        margin-top: 0.25rem;
        display: block;
    }
    .back-link {
        text-align: center;
        margin-top: 1.5rem;
    }
    .back-link a {
        color: var(--color-primary);
        text-decoration: none;
    }
    .back-link a:hover {
        text-decoration: underline;
    }
</style>
@endpush

@section('content')
<div class="reset-container">
    <div class="reset-card">
        <h1>{{ __('messages.forgot_password') }}</h1>
        <p>{{ __('messages.enter_email_reset_link') }}</p>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <label for="email">{{ __('messages.email_address') }}</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-primary">{{ __('messages.send_reset_link') }}</button>
        </form>

        <div class="back-link">
            <a href="{{ route('signin') }}">{{ __('messages.back_to_signin') }}</a>
        </div>
    </div>
</div>
@endsection