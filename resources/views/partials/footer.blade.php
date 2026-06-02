@php
    use Illuminate\Support\Facades\Route;

    $locale        = app()->getLocale();
    $siteName      = $settings['site_name']    ?? config('app.name');
    $siteEmail     = $settings['site_email']   ?? 'support@thestart.dz';
    $sitePhone     = $settings['site_phone']   ?? '+213 123 45 67 89';
    $siteAddress   = $settings['site_address'] ?? '123 Algiers Street, Algiers, Algeria 16000';

    $aboutTextKey      = 'footer_about_text_' . $locale;
    $aboutTextFallback = 'footer_about_text_en';
    $aboutText = $settings[$aboutTextKey] ?? $settings[$aboutTextFallback] ?? __('messages.about_footer_default');

    $quickLinks = isset($settings['footer_quick_links']) && $settings['footer_quick_links']
        ? json_decode($settings['footer_quick_links'], true) : null;
    $quickLinks = is_array($quickLinks) ? $quickLinks : [
        ['label' => __('messages.about_us'),        'url' => route('about')],
        ['label' => __('messages.contact_us'),       'url' => route('contact')],
        ['label' => __('messages.shipping_policy'),  'url' => route('shipping.policy')],
    ];

    $customerLinks = isset($settings['footer_customer_service']) && $settings['footer_customer_service']
        ? json_decode($settings['footer_customer_service'], true) : null;
    $customerLinks = is_array($customerLinks) ? $customerLinks : [
        ['label' => __('messages.returns'),          'url' => route('return.policy')],
        ['label' => __('messages.order_tracking'),   'url' => Route::has('orders.index') ? route('orders.index') : route('contact')],
        ['label' => __('messages.terms_conditions'), 'url' => route('terms')],
        ['label' => __('messages.privacy_policy'),   'url' => route('privacy')],
    ];

    {{-- Bug fix: use date('Y') so copyright auto-updates every year --}}
    $copyrightText = $settings['footer_copyright'] ?? '© ' . date('Y') . ' ' . $siteName . '. ' . __('messages.all_rights_reserved');

    $facebook  = $settings['facebook_url']  ?? '';
    $instagram = $settings['instagram_url'] ?? '';
    $twitter   = $settings['twitter_url']   ?? '';
    $youtube   = $settings['youtube_url']   ?? '';
@endphp

<footer class="footer">
    <div class="footer-container">
        <div class="footer-content">

            {{-- About / General Info --}}
            <div class="footer-section" data-reveal>
                <h3>{{ __('messages.about_the_start') }}</h3>
                <p>{{ $aboutText }}</p>
                <p><strong>{{ __('messages.address') }}:</strong> {{ $siteAddress }}</p>
                <p>
                    <strong>{{ __('messages.phone') }}:</strong>
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $sitePhone) }}">{{ $sitePhone }}</a>
                </p>
                <p>
                    <strong>{{ __('messages.email') }}:</strong>
                    <a href="mailto:{{ $siteEmail }}">{{ $siteEmail }}</a>
                </p>
            </div>

            {{-- Quick Links --}}
            <div class="footer-section" data-reveal>
                <h3>{{ __('messages.quick_links') }}</h3>
                @foreach($quickLinks as $link)
                    <p><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></p>
                @endforeach
            </div>

            {{-- Customer Service --}}
            <div class="footer-section" data-reveal>
                <h3>{{ __('messages.customer_service') }}</h3>
                @foreach($customerLinks as $link)
                    <p><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></p>
                @endforeach
            </div>

            {{-- Social Media --}}
            <div class="footer-section" data-reveal>
                <h3>{{ __('messages.follow_us') }}</h3>
                <div class="footer-social">
                    @if($facebook)
                        <a href="{{ $facebook }}" target="_blank" rel="noopener" aria-label="Facebook" class="social-link social-facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    @endif
                    @if($instagram)
                        <a href="{{ $instagram }}" target="_blank" rel="noopener" aria-label="Instagram" class="social-link social-instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    @endif
                    @if($twitter)
                        <a href="{{ $twitter }}" target="_blank" rel="noopener" aria-label="X / Twitter" class="social-link social-twitter">
                            <i class="fab fa-x-twitter"></i>
                        </a>
                    @endif
                    @if($youtube)
                        <a href="{{ $youtube }}" target="_blank" rel="noopener" aria-label="YouTube" class="social-link social-youtube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    @endif
                    @if(!$facebook && !$instagram && !$twitter && !$youtube)
                        <p class="footer-social-placeholder">{{ __('messages.coming_soon') }}</p>
                    @endif
                </div>
            </div>

        </div>

        <div class="footer-bottom">
            <p>{{ $copyrightText }}</p>
        </div>
    </div>
</footer>

@push('styles')
    @vite('resources/css/footer.css')
    <style>
    /* ── Social icons in footer ── */
    .footer-social {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 8px;
    }
    .social-link {
        width: 38px; height: 38px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
        background: rgba(255,255,255,0.08);
        color: inherit;
        transition: transform .25s ease, background .25s ease, color .25s ease;
        text-decoration: none;
    }
    .social-link:hover { transform: translateY(-3px) scale(1.1); }
    .social-link.social-facebook:hover  { background: #1877f2; color: #fff; }
    .social-link.social-instagram:hover { background: linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888); color: #fff; }
    .social-link.social-twitter:hover   { background: #000; color: #fff; }
    .social-link.social-youtube:hover   { background: #ff0000; color: #fff; }
    </style>
@endpush