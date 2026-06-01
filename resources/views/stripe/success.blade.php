@extends('layouts.app')

@section('content')
    <div class="container py-6">
        <h1 class="mb-4">Thank you</h1>
        <p>{{ $message ?? 'Payment received. Your order is being processed.' }}</p>
        <p>If you don't receive a confirmation email within a few minutes, please contact support.</p>
        <a href="{{ route('home') }}" class="btn btn-primary mt-4">Continue Shopping</a>
    </div>
@endsection
