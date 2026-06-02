@extends('layouts.app')

@section('title', $settings['about_page_title_' . app()->getLocale()] ?? $settings['about_page_title_en'] ?? __('messages.about_page_title'))

@push('styles')
    @vite('resources/css/about.css')
@endpush

@section('content')
<div class="about-page">
    <div class="container">

        <!-- Hero Section (fully bilingual via $about) -->
        <div class="hero-section">
            <div class="hero-section-inner">
                <h1 class="hero-section-title">{{ $about['hero']['title'] }}</h1>
                <div class="hero-tagline">{{ $about['hero']['tagline'] }}</div>
                <p class="hero-description">{{ $about['hero']['description'] }}</p>
            </div>
            <div class="hero-section-decoration"></div>
        </div>

        <!-- Mission & Vision -->
        <div class="mission-vision">
            <div class="mv-card">
                <div class="mv-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                </div>
                <h2>{{ $about['mission']['title'] }}</h2>
                <p>{{ $about['mission']['text'] }}</p>
            </div>
            <div class="mv-card">
                <div class="mv-icon">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </div>
                <h2>{{ $about['vision']['title'] }}</h2>
                <p>{{ $about['vision']['text'] }}</p>
            </div>
        </div>

        <!-- Our Story -->
        <div class="story-section">
            <div class="story-content">
                <h2>{{ $about['story']['title'] }}</h2>
                <div class="story-subtitle">{{ $about['story']['subtitle'] }}</div>
                {!! nl2br(e($about['story']['text'])) !!}
            </div>
        </div>

        <!-- Our Values (heading bilingual from settings) -->
        @php
            $locale = app()->getLocale();
            $valuesHeading = $settings['about_values_heading_' . $locale] ?? $settings['about_values_heading_en'] ?? __('messages.our_core_values');
        @endphp
        <div class="values-section">
            <h2>{{ $valuesHeading }}</h2>
            <div class="values-grid">
                @foreach($about['values'] as $value)
                <div class="value-card">
                    <div class="value-icon">
                        <i class="{{ $value['icon'] ?? 'fas fa-star' }}"></i>
                    </div>
                    <h3>{{ $value['title'] }}</h3>
                    <p>{{ $value['text'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Why Choose Us (bilingual headings) -->
        @php
            $featuresHeading = $settings['about_features_heading_' . $locale] ?? $settings['about_features_heading_en'] ?? __('messages.why_choose_us');
            $featuresSubtitle = $settings['about_features_subtitle_' . $locale] ?? $settings['about_features_subtitle_en'] ?? __('messages.experience_ecommerce');
        @endphp
        <div class="features-section">
            <h2>{{ $featuresHeading }}</h2>
            <div class="features-subtitle">{{ $featuresSubtitle }}</div>
            <div class="features-grid">
                @foreach($about['features'] as $feature)
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="{{ $feature['icon'] ?? 'fas fa-check' }}"></i>
                    </div>
                    <h3>{{ $feature['title'] }}</h3>
                    <p>{{ $feature['text'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Our Team -->
        @if(count($about['team']) > 0)
            @php
                $teamHeading = $settings['about_team_heading_' . $locale] ?? $settings['about_team_heading_en'] ?? __('messages.meet_our_team');
                $teamSubtitle = $settings['about_team_subtitle_' . $locale] ?? $settings['about_team_subtitle_en'] ?? __('messages.passionate_people');
            @endphp
            <div class="team-section">
                <h2>{{ $teamHeading }}</h2>
                <div class="team-subtitle">{{ $teamSubtitle }}</div>
                <div class="team-grid">
                    @foreach($about['team'] as $member)
                        <div class="team-card">
                            <div class="team-image">
                                @if($member['image'])
                                    <img src="{{ asset($member['image']) }}" alt="{{ $member['name'] }}">
                                @else
                                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                @endif
                            </div>
                            <h3>{{ $member['name'] }}</h3>
                            <div class="team-role">{{ $member['role'] }}</div>
                            <p class="team-bio">{{ $member['bio'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Call to Action (fully bilingual via $about) -->
        <div class="cta-section">
            <h2>{{ $about['cta']['title'] }}</h2>
            <p>{{ $about['cta']['text'] }}</p>
            <a href="{{ $about['cta']['button_link'] }}" class="cta-button">{{ $about['cta']['button_text'] }}</a>
        </div>
    </div>
</div>
@endsection