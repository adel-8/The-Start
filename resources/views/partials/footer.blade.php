@php
    use Illuminate\Support\Facades\Route;

    $locale = app()->getLocale();
    $siteName   = $settings['site_name'] ?? config('app.name');
    $siteEmail  = $settings['site_email'] ?? 'support@thestart.com';
    $sitePhone  = $settings['site_phone'] ?? '+213 123 45 67 89';
    $siteAddress = $settings['site_address'] ?? '123 Algiers Street, Algiers, Algeria 16000';

    // Bilingual footer about text
    $aboutTextKey = 'footer_about_text_' . $locale;
    $aboutTextFallback = 'footer_about_text_en';
    $aboutText = $settings[$aboutTextKey] ?? $settings[$aboutTextFallback] ?? __('messages.about_footer_default');

    // Quick links (JSON) – assumed to be the same for both languages or already bilingual in stored JSON
    $quickLinks = isset($settings['footer_quick_links']) && $settings['footer_quick_links']
        ? json_decode($settings['footer_quick_links'], true)
        : null;
    $quickLinks = is_array($quickLinks) ? $quickLinks : [
        ['label' => __('messages.about_us'), 'url' => route('about')],
        ['label' => __('messages.contact_us'), 'url' => route('contact')],
        ['label' => __('messages.faqs'), 'url' => route('about')], // change if you have a FAQ page
        ['label' => __('messages.shipping_policy'), 'url' => route('shipping.policy')]
    ];

    // Customer service links (JSON) – same assumption
    $customerLinks = isset($settings['footer_customer_service']) && $settings['footer_customer_service']
        ? json_decode($settings['footer_customer_service'], true)
        : null;
 
    $customerLinks = is_array($customerLinks) ? $customerLinks : [
        ['label' => __('messages.returns'), 'url' => route('return.policy')],
        ['label' => __('messages.order_tracking'), 'url' => Route::has('orders.index') ? route('orders.index') : route('contact')],
        ['label' => __('messages.terms_conditions'), 'url' => route('terms')],
        ['label' => __('messages.privacy_policy'), 'url' => route('privacy')]
    ];

    $copyrightText = $settings['footer_copyright'] ?? __('messages.copyright_default');

    $facebook  = $settings['facebook_url'] ?? '';
    $instagram = $settings['instagram_url'] ?? '';
    $twitter   = $settings['twitter_url'] ?? '';
    $youtube   = $settings['youtube_url'] ?? '';
@endphp

<footer class="footer">
    <div class="footer-container">
        <div class="footer-content">
            <!-- About / General Info -->
            <div class="footer-section">
                <h3>{{ __('messages.about_the_start') }}</h3>
                <p>{{ $aboutText }}</p>
                <p><strong>{{ __('messages.address') }}:</strong> {{ $siteAddress }}</p>
                <p><strong>{{ __('messages.phone') }}:</strong> <a href="tel:{{ preg_replace('/[^0-9+]/', '', $sitePhone) }}">{{ $sitePhone }}</a></p>
                <p><strong>{{ __('messages.email') }}:</strong> <a href="mailto:{{ $siteEmail }}">{{ $siteEmail }}</a></p>
            </div>

            <!-- Quick Links -->
            <div class="footer-section">
                <h3>{{ __('messages.quick_links') }}</h3>
                @foreach($quickLinks as $link)
                    <p><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></p>
                @endforeach
            </div>

            <!-- Customer Service -->
            <div class="footer-section">
                <h3>{{ __('messages.customer_service') }}</h3>
                @foreach($customerLinks as $link)
                    <p><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></p>
                @endforeach
            </div>

            <!-- Social Media -->
            <div class="footer-section">
                <h3>{{ __('messages.follow_us') }}</h3>
                @if($facebook)
                    <p><a href="{{ $facebook }}" target="_blank" rel="noopener">{{ __('messages.facebook') }}</a></p>
                @endif
                @if($instagram)
                    <p><a href="{{ $instagram }}" target="_blank" rel="noopener">{{ __('messages.instagram') }}</a></p>
                @endif
                @if($twitter)
                    <p><a href="{{ $twitter }}" target="_blank" rel="noopener">{{ __('messages.twitter') }}</a></p>
                @endif
                @if($youtube)
                    <p><a href="{{ $youtube }}" target="_blank" rel="noopener">{{ __('messages.youtube') }}</a></p>
                @endif
            </div>
        </div>

        <div class="footer-bottom">
            <p>{{ $copyrightText }}</p>
        </div>
    </div>
</footer>

@push('styles')
    @vite('resources/css/footer.css')
@endpush