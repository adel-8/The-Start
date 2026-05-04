@extends('layouts.app')

@section('title', __('messages.add_address'))

@push('styles')
    @vite('resources/css/addresses.css')
@endpush

@section('content')
<div class="addresses-page">
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-plus-circle"></i> {{ __('messages.add_address') }}</h1>
            <a href="{{ route('addresses.index') }}" class="btn-secondary">{{ __('messages.cancel') }}</a>
        </div>

        <div class="address-form-card">
            <form action="{{ route('addresses.store') }}" method="POST">
                @csrf
                @include('addresses.form')
                <div class="form-actions">
                    <button type="submit" class="btn-primary">{{ __('messages.save_address') }}</button>
                    <a href="{{ route('addresses.index') }}" class="btn-secondary">{{ __('messages.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection