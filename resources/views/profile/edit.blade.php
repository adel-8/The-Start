@extends('layouts.app')

@section('title', __('messages.edit_profile'))

@push('styles')
    <style>
        .edit-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 1.5rem;
        }
        .form-card {
            background: var(--color-surface);
            border-radius: 32px;
            padding: 2rem;
            border: 1px solid var(--color-border);
            box-shadow: var(--shadow-md);
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--color-text);
        }
        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid var(--color-border);
            border-radius: 1rem;
            font-family: inherit;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 2px rgba(100,95,125,0.2);
        }
        .btn-save {
            background: var(--color-primary);
            color: white;
            border: none;
            padding: 0.8rem 1.8rem;
            border-radius: 2rem;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-save:hover {
            background: var(--color-primary-hover);
        }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--color-border);
            color: var(--color-text);
            padding: 0.8rem 1.8rem;
            border-radius: 2rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-outline:hover {
            background: #f5f3ec;
        }
        .current-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }
        .alert {
            padding: 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
        }
        .alert-danger {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        .alert-danger ul {
            margin: 0;
            padding-left: 1.2rem;
        }
    </style>
@endpush

@section('content')
<div class="edit-container">
    <h1>{{ __('messages.edit_profile') }}</h1>
    <p>{{ __('messages.update_personal_info') }}</p>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="form-card">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">{{ __('messages.profile_picture') }}</label>
            @if($user->profile_image)
                <img src="{{ asset('storage/' . $user->profile_image) }}" class="current-avatar" alt="{{ __('messages.avatar') }}">
            @endif
            <input type="file" name="profile_image" class="form-control" accept="image/*">
            <small>{{ __('messages.leave_empty_keep_current') }}</small>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.full_name') }}</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name ?? $user->username) }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.email_address') }}</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.phone_number') }}</label>
            <input type="tel" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.age') }}</label>
            <input type="number" name="age" class="form-control" value="{{ old('age', $user->age) }}" min="18" max="120">
        </div>

        <div class="form-group">
            <button type="submit" class="btn-save">{{ __('messages.save_changes') }}</button>
            <a href="{{ route('profile.show') }}" class="btn-outline">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection