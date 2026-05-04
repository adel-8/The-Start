@extends('layouts.app')

@section('title', $settings['contact_page_title'] ?? __('messages.contact_page_title'))

@push('styles')
    @vite('resources/css/contact.css')
@endpush

@section('content')
<div class="contact-page">
    <div class="container">

        <!-- Page Header -->
        <div class="page-header">
            <h1>{{ $settings['contact_heading'] ?? __('messages.contact_us') }}</h1>
            <p>{{ $settings['contact_description'] ?? __('messages.contact_page_description') }}</p>
        </div>

        <div class="contact-grid">
            <!-- Contact Info -->
            <div class="contact-info">
                <div class="info-card">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                    </div>
                    <h3>{{ __('messages.phone') }}</h3>
                    <p><a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a></p>
                    <p class="hours">{{ __('messages.working_hours') }}</p>
                </div>

                <div class="info-card">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                    <h3>{{ __('messages.email') }}</h3>
                    <p><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></p>
                    @if($contact['email2'])
                        <p><a href="mailto:{{ $contact['email2'] }}">{{ $contact['email2'] }}</a></p>
                    @endif
                </div>

                <div class="info-card">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <h3>{{ __('messages.address') }}</h3>
                    <p>{{ nl2br(e($contact['address'])) }}</p>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-container">
                <div id="alertMessage" class="alert alert-success" style="display: none;"></div>
                
                <form id="contactForm" class="contact-form" method="POST" action="{{ route('contact.store') }}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">{{ __('messages.full_name') }} <span class="required">*</span></label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">{{ __('messages.email_address') }} <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">{{ __('messages.phone') }}</label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">{{ __('messages.subject') }} <span class="required">*</span></label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="message">{{ __('messages.message') }} <span class="required">*</span></label>
                        <textarea id="message" name="message" rows="6" required></textarea>
                    </div>

                    <button type="submit" class="btn-submit">{{ $settings['contact_submit_button_text'] ?? __('messages.send_message') }}</button>
                </form>
            </div>
        </div>

        <!-- Map Section -->
        <div class="map-section">
            <iframe 
                src="{{ $contact['map_url'] }}"
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section">
            <h2>{{ __('messages.faq') }}</h2>
            <div class="faq-grid">
                @foreach($faq as $item)
                    <div class="faq-item">
                        <h3>{{ $item['question'] }}</h3>
                        <p>{{ $item['answer'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('contactForm');
        const alertDiv = document.getElementById('alertMessage');
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                alertDiv.style.display = 'block';
                alertDiv.className = data.success ? 'alert alert-success' : 'alert alert-danger';
                alertDiv.textContent = data.message;
                if (data.success) {
                    form.reset();
                }
                setTimeout(() => {
                    alertDiv.style.display = 'none';
                }, 5000);
            })
            .catch(error => {
                alertDiv.style.display = 'block';
                alertDiv.className = 'alert alert-danger';
                alertDiv.textContent = 'Network error, please try again.';
                setTimeout(() => {
                    alertDiv.style.display = 'none';
                }, 5000);
            });
        });
    });
</script>
@endpush