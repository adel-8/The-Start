@extends('layouts.app')

@section('title', $title . ' · ' . ($settings['site_name'] ?? config('app.name', 'The Start')))

@push('styles')
<style>
    .return-policy-page {
        margin: 3rem auto;
        max-width: 900px;
        padding: 0 1.5rem;
    }
    .return-policy-page h1 {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        color: #1a1a1a;
        border-bottom: 2px solid #645F7D;
        padding-bottom: 0.5rem;
        display: inline-block;
    }
    .return-content {
        background: #ffffff;
        padding: 2rem;
        border-radius: 1rem;
        margin: 1.5rem 0;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        line-height: 1.6;
        color: #334155;
    }
    .return-content p { margin-bottom: 1rem; }
    .return-content h2 { font-size: 1.5rem; margin: 1.5rem 0 1rem; color: #1a1a1a; }
    .return-content h3 { font-size: 1.25rem; margin: 1.25rem 0 0.75rem; color: #1a1a1a; }
    .return-content ul, .return-content ol { margin: 1rem 0 1rem 1.5rem; }
    .return-content li { margin-bottom: 0.5rem; }
    .return-content a { color: #645F7D; text-decoration: underline; }
    .back-link { text-align: center; margin-top: 2rem; }
    .back-link .btn-secondary {
        display: inline-block;
        padding: 0.6rem 1.5rem;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 0.5rem;
        color: #1a1a1a;
        text-decoration: none;
        transition: all 0.2s;
    }
    .back-link .btn-secondary:hover {
        background: #645F7D;
        color: white;
        border-color: #645F7D;
    }
    @media (max-width: 768px) {
        .return-policy-page { margin: 1.5rem auto; padding: 0 1rem; }
        .return-content { padding: 1rem; }
        .return-policy-page h1 { font-size: 1.5rem; }
    }
</style>
@endpush

@section('content')
<div class="return-policy-page">
    <div class="container">
        <h1>{{ $title }}</h1>
        <div class="return-content">
            {!! nl2br(e($content)) !!}
        </div>
        <div class="back-link">
            <a href="{{ route('home') }}" class="btn-secondary">{{ __('messages.back_to_home') }}</a>
        </div>
    </div>
</div>
@endsection