@extends('layouts.app')

@section('title', 'Payment Verification')

@section('content')
    <section class="payment-verification">
        <div class="container">
            <h1>Payment Received</h1>
            <p>Your payment has been received and is being verified by Stripe.</p>
            <p>If your order is ready, you will be redirected automatically. Otherwise please check your email for confirmation or contact support.</p>
            <p><strong>Session ID:</strong> {{ $sessionId }}</p>
            <p><a href="{{ route('cart') }}">Return to cart</a></p>
        </div>
    </section>
@endsection
