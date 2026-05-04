@extends('admin.layouts.app')

@section('title', __('admin.edit_customer') . ': ' . ($user->name ?? $user->username))

@section('content')
<div class="admin-header">
    <h1>{{ __('admin.edit_customer') }}: {{ $user->name ?? $user->username }}</h1>
</div>

<form action="{{ route('admin.users.update', $user) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="name">{{ __('admin.name') }}</label>
        <input type="text" name="name" id="name" value="{{ old('name', $user->name ?? $user->username) }}" required>
    </div>
    <div class="form-group">
        <label for="email">{{ __('admin.email') }}</label>
        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required>
    </div>
    <div class="form-group">
        <label for="role_id">{{ __('admin.role') }}</label>
        <select name="role_id" id="role_id">
            <option value="3" {{ $user->role_id == 3 ? 'selected' : '' }}>{{ __('admin.role_customer') }}</option>
            <option value="2" {{ $user->role_id == 2 ? 'selected' : '' }}>{{ __('admin.role_admin') }}</option>
            <option value="1" {{ $user->role_id == 1 ? 'selected' : '' }}>{{ __('admin.role_owner') }}</option>
        </select>
    </div>
    <button type="submit" class="btn-primary">{{ __('admin.update') }}</button>
    <a href="{{ route('admin.users.index') }}" class="btn-secondary">{{ __('admin.cancel') }}</a>
</form>
@endsection