@extends('layouts.app')

@section('title', $settings['baridimob_page_title'] ?? __('messages.baridimob_page_title'))


@push('styles')
<style>
    .payment-container {
        max-width: 800px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }
    .payment-card {
        background: var(--color-surface);
        border-radius: 1.5rem;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--color-border);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .payment-header {
        background: var(--color-primary);
        color: white;
        padding: 1.5rem;
        text-align: center;
    }
    .payment-header h1 {
        font-size: 1.8rem;
        margin: 0;
    }
    .payment-body {
        padding: 2rem;
    }
    .order-details {
        background: #f8f9fc;
        border-radius: 1rem;
        padding: 1.2rem;
        margin-bottom: 1.5rem;
    }
    .bank-details {
        background: #fff8e7;
        border-left: 4px solid var(--color-accent);
        padding: 1rem;
        margin: 1.5rem 0;
    }
    .upload-section {
        border-top: 1px solid var(--color-border);
        padding-top: 1.5rem;
        margin-top: 1.5rem;
    }
    .file-input {
        margin: 1rem 0;
    }
    .btn-primary {
        background: var(--color-primary);
        color: white;
        padding: 0.6rem 1.2rem;
        border-radius: 2rem;
        text-decoration: none;
        display: inline-block;
        transition: 0.2s;
    }
    .btn-primary:hover {
        background: var(--color-primary-hover);
        transform: translateY(-2px);
    }
    .btn-outline {
        background: transparent;
        border: 1px solid var(--color-primary);
        color: var(--color-primary);
        padding: 0.6rem 1.2rem;
        border-radius: 2rem;
        text-decoration: none;
        display: inline-block;
        transition: 0.2s;
    }
    .btn-outline:hover {
        background: var(--color-primary);
        color: white;
    }
    .alert {
        padding: 1rem;
        border-radius: 0.75rem;
        margin-bottom: 1rem;
    }
    .alert-success {
        background: #e9f7ef;
        border-left: 4px solid #2c8f5e;
        color: #155724;
    }
    .text-muted {
        color: var(--color-muted);
        font-size: 0.85rem;
    }
    hr {
        margin: 1rem 0;
        border: none;
        border-top: 1px solid var(--color-border);
    }
    .badge {
        padding: 0.2rem 0.6rem;
        border-radius: 40px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .badge-warning {
        background: #f0ad4e;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="payment-container">
    <div class="payment-card">
        <div class="payment-header">
            <h1>{{ $settings['baridimob_title'] ?? __('messages.baridimob_title') }}</h1>
            <p>{{ $settings['baridimob_subtitle'] ?? __('messages.baridimob_subtitle') }}</p>
        </div>
        <div class="payment-body">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="order-details">
                <h3>{{ __('messages.pending_order') }}</h3>
                <p><strong>{{ __('messages.total_amount') }}:</strong> {{ format_currency($total) }}</p>
                <p><strong>{{ __('messages.shipping') }}:</strong> {{ format_currency($shippingCost) }}</p>
                <p><strong>{{ __('messages.subtotal') }}:</strong> {{ format_currency($subtotal - ($discount ?? 0)) }}</p>
                @if(isset($discount) && $discount > 0)
                    <p><strong>{{ __('messages.discount') }}:</strong> -{{ format_currency($discount) }}</p>
                @endif
                <p><strong>{{ __('messages.items') }}:</strong> {{ count($cart) }}</p>
                @if(!empty($checkoutData['coupon_code']))
                    <p><strong>{{ __('messages.coupon') }}:</strong> {{ $checkoutData['coupon_code'] }}</p>
                @endif
                <p><strong>{{ __('messages.status') }}:</strong> <span class="badge badge-warning">{{ __('messages.awaiting_payment') }}</span></p>
            </div>

            <h3>{{ __('messages.how_to_pay') }}</h3>
            <ol>
                <li>{{ __('messages.step1') }}</li>
                <li>{{ __('messages.step2') }}</li>
                <li>{{ __('messages.step3', ['reference' => 'ORD-XXXXXX']) }}</li>
                <li>{{ __('messages.step4') }}</li>
                <li>{{ __('messages.step5') }}</li>
            </ol>

            <div class="bank-details">
                <p><strong>{{ __('messages.account_name') }}:</strong> {{ $settings['baridimob_account_name'] ?? 'The Start E-commerce' }}</p>
                <p><strong>{{ __('messages.account_number') }}:</strong> {{ $settings['baridimob_account'] ?? '123 456 789 01' }}</p>
                <p><strong>{{ __('messages.bank') }}:</strong> {{ $settings['baridimob_bank'] ?? 'Algerian Post (BaridiMob)' }}</p>
                <p><strong>{{ __('messages.amount') }}:</strong> {{ format_currency($total) }}</p>
            </div>

            <div class="upload-section">
                <h3>{{ __('messages.upload_proof') }}</h3>
                <p class="text-muted">{{ __('messages.upload_proof_desc') }}</p>
                <form action="{{ route('payment.baridimob.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="file-input">
                        <input type="file" name="proof" accept="image/*,application/pdf" required>
                    </div>
                    <button type="submit" class="btn-primary">{{ __('messages.upload_button') }}</button>
                </form>
            </div>

            <hr>
            <div class="text-center">
                <a href="{{ route('/') }}" class="btn-outline">{{ __('messages.continue_shopping') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection