@extends('layouts.app')

@section('title', __('messages.edit_address'))

@push('styles')
    @vite('resources/css/addresses.css')
@endpush

@section('content')
<div class="addresses-page">
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-edit"></i> {{ __('messages.edit_address') }}</h1>
            <a href="{{ route('addresses.index') }}" class="btn-secondary">{{ __('messages.cancel') }}</a>
        </div>

        <div class="address-form-card">
            <form action="{{ route('addresses.update', $address) }}" method="POST">
                @csrf
                @method('PUT')
                @include('addresses.form')
                <div class="form-actions">
                    <button type="submit" class="btn-primary">{{ __('messages.update_address') }}</button>
                    <a href="{{ route('addresses.index') }}" class="btn-secondary">{{ __('messages.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection