@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="legal-page">
    <div class="container">
        <h1>{{ $title }}</h1>
        <div class="legal-content">
            {!! nl2br(e($content)) !!}
        </div>
        <div class="back-link">
            <a href="{{ url()->previous() }}" class="btn-secondary">{{ __('messages.back') }}</a>
        </div>
    </div>
</div>

@push('styles')
<style>
    .legal-page {
        margin: 2rem auto;
        max-width: 800px;
        padding: 0 1rem;
    }
    .legal-content {
        background: var(--color-surface);
        padding: 2rem;
        border-radius: 1rem;
        margin: 1rem 0;
        border: 1px solid var(--color-border);
    }
    .back-link {
        text-align: center;
        margin-top: 1rem;
    }
</style>
@endpush
@endsection