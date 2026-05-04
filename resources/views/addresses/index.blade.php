@extends('layouts.app')

@section('title', __('messages.my_addresses'))

@push('styles')
    @vite('resources/css/addresses.css')
@endpush

@section('content')
<div class="addresses-page">
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-map-marker-alt"></i> {{ __('messages.my_addresses') }}</h1>
            <a href="{{ route('addresses.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i> {{ __('messages.add_new_address') }}
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($addresses->count())
            <div class="addresses-grid">
                @foreach($addresses as $address)
                    <div class="address-card">
                        <div class="address-card-header">
                            <div class="address-badges">
                                @if($address->is_default)
                                    <span class="badge badge-default">{{ __('messages.default') }}</span>
                                @endif
                            </div>
                            <div class="address-actions">
                                <a href="{{ route('addresses.edit', $address) }}" class="btn-icon" title="{{ __('messages.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('addresses.destroy', $address) }}" method="POST" class="inline-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon delete-address-btn" data-confirm="{{ __('messages.delete_address_confirm') }}" title="{{ __('messages.delete') }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @if(!$address->is_default)
                                    <form action="{{ route('addresses.set-default', $address) }}" method="POST" class="inline-form">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn-icon" title="{{ __('messages.set_default') }}">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <div class="address-card-body">
                            <div class="address-line">{{ $address->address_line1 }}</div>
                            @if($address->address_line2)
                                <div class="address-line">{{ $address->address_line2 }}</div>
                            @endif
                            <div class="address-line">
                                {{ $address->city }}, {{ $address->state ?? '' }} {{ $address->postal_code }}
                            </div>
                            <div class="address-line">{{ $address->country }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-map-marker-alt"></i>
                <p>{{ __('messages.no_addresses') }}</p>
                <a href="{{ route('addresses.create') }}" class="btn-primary">{{ __('messages.add_first_address') }}</a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.delete-address-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const confirmMsg = btn.getAttribute('data-confirm');
            if (!confirm(confirmMsg)) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush