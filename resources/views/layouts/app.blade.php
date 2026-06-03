<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
      dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}"
      data-currency-symbol="{{ __('messages.currency_symbol') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'The&Start'))</title>

    {{-- Premium Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
    /* ── Gold design tokens ── */
    :root {
        --gold:       #C9A96E;
        --gold-light: #E8D5A3;
        --gold-dark:  #8B6914;
        --gold-glow:  rgba(201,169,110,0.35);
    }

    /* ── Premium typography ── */
    h1,h2,h3,h4,h5,h6 { font-family: 'Cormorant Garamond', serif; }
    [dir="rtl"] h1,[dir="rtl"] h2,[dir="rtl"] h3,
    [dir="rtl"] h4,[dir="rtl"] h5,[dir="rtl"] h6,
    [dir="rtl"] body,[dir="rtl"] p,[dir="rtl"] a,
    [dir="rtl"] button,[dir="rtl"] input,
    [dir="rtl"] select,[dir="rtl"] textarea,
    .arabic { font-family: 'Cairo', sans-serif; }

    /* ── Scroll-reveal ── */
    [data-reveal] {
        opacity: 0;
        transform: translateY(24px);
        transition: opacity .55s ease, transform .55s ease;
    }
    [data-reveal].revealed { opacity: 1; transform: translateY(0); }

    /* ── Gold colours ── */
    .product-price { color: var(--gold-dark); font-weight: 700; }
    .star,.star-filled { color: var(--gold); display: inline-block; }
    .star-empty { color: #d1d5db; display: inline-block; }

    /* ── Animated stars ── */
    @keyframes starPop {
        0%   { transform: scale(0) rotate(-20deg); opacity: 0; }
        60%  { transform: scale(1.35) rotate(5deg); }
        100% { transform: scale(1) rotate(0); opacity: 1; }
    }
    .star { animation: starPop .4s ease both; }
    .star:nth-child(1){animation-delay:.08s}
    .star:nth-child(2){animation-delay:.16s}
    .star:nth-child(3){animation-delay:.24s}
    .star:nth-child(4){animation-delay:.32s}
    .star:nth-child(5){animation-delay:.40s}

    /* ── Cart badge bounce ── */
    @keyframes cartBounce {
        0%{transform:scale(1)} 30%{transform:scale(1.5)}
        60%{transform:scale(.85)} 80%{transform:scale(1.15)} 100%{transform:scale(1)}
    }
    .cart-count.bounce { animation: cartBounce .5s cubic-bezier(.36,.07,.19,.97) both; }

    /* ── Add-to-cart states ── */
    .add-cart-btn { transition: background .25s, transform .1s, box-shadow .25s; }
    .add-cart-btn.btn-success {
        background: #16a34a !important; color: #fff !important;
        box-shadow: 0 4px 14px rgba(22,163,74,.35);
    }
    .add-cart-btn:not(:disabled):active { transform: scale(.96); }

    /* ── Qty micro-bounce ── */
    @keyframes qtyPress {
        0%{transform:scale(1)} 40%{transform:scale(.86)} 100%{transform:scale(1)}
    }
    .qty-btn.pressing { animation: qtyPress .18s ease; }

    /* ── Shimmer on CTA ── */
    @keyframes shimmer { 0%{left:-75%} 100%{left:125%} }
    .btn-shimmer { position: relative; overflow: hidden; }
    .btn-shimmer::after {
        content: ''; position: absolute; top: 0; left: -75%;
        width: 50%; height: 100%;
        background: linear-gradient(120deg,transparent 0%,rgba(255,255,255,.35) 50%,transparent 100%);
        animation: shimmer 3s ease-in-out infinite;
        animation-delay: 1.5s;
        pointer-events: none;
    }

    /* ── CTA glow pulse ── */
    @keyframes glowPulse {
        0%,100%{box-shadow:0 0 0 0 var(--gold-glow)}
        50%{box-shadow:0 0 0 10px transparent}
    }
    .btn-glow { animation: glowPulse 2.2s ease-in-out infinite; }

    /* ── CTA animated gradient ── */
    @keyframes gradientShift {
        0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%}
    }
    .cta-section {
        background: linear-gradient(135deg,#0f0f0f,#1a1410,#0f0f0f,#221808) !important;
        background-size: 300% 300% !important;
        animation: gradientShift 8s ease infinite;
        color: #fff;
        border-color: transparent !important;
    }
    .cta-section h2, .cta-section p { color: #fff !important; }

    /* ── Section heading animated underline ── */
    .section-title { position: relative; display: inline-block; }
    .section-title::after {
        content: ''; position: absolute; bottom: -6px; left: 0;
        width: 0; height: 2px; background: var(--gold);
        transition: width .7s ease .3s;
    }
    [dir="rtl"] .section-title::after { left: auto; right: 0; }
    .section-title.revealed::after { width: 100%; }

    /* ────────────────────────────────────────
       HERO FIXES
       – Fix right:50% that breaks centering
       – Add Ken Burns only (no crossfade that
         conflicts with home.css display:none)
    ──────────────────────────────────────── */

    /* Fix: right:50% in home.css breaks hero centering in both LTR and RTL */
    .hero-content {
        right: auto !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    /* Keep the blade animation from home.css working */
    .hero-slide.active .hero-content {
        animation: heroContentIn .7s ease .3s both;
    }
    @keyframes heroContentIn {
        0%   { opacity:0; transform: translate(-50%, calc(-50% + 20px)); }
        100% { opacity:1; transform: translate(-50%, -50%); }
    }

    /* Ken Burns — only on the image, no position changes to the slide */
    @keyframes kenBurns { 0%{transform:scale(1)} 100%{transform:scale(1.08)} }
    .hero-slide.active .hero-slide-img {
        animation: kenBurns 6s ease-in-out forwards;
    }

    /* ── Dot indicator gold when active ── */
    .dot.active {
        background: var(--gold) !important;
        border-color: var(--gold) !important;
    }
    </style>

    @stack('styles')
</head>
<body dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

    @include('partials.navbar')
    <main>@yield('content')</main>
    @include('partials.footer')

    <script>
    document.addEventListener('DOMContentLoaded', () => {

        /* 1. Scroll-reveal */
        const revealEls = document.querySelectorAll('[data-reveal]');
        if (revealEls.length) {
            const obs = new IntersectionObserver((entries) => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        e.target.classList.add('revealed');
                        obs.unobserve(e.target);
                    }
                });
            }, { threshold: 0.12 });
            revealEls.forEach((el, i) => {
                if (!el.style.transitionDelay) el.style.transitionDelay = (i % 4 * 90) + 'ms';
                obs.observe(el);
            });
        }

        /* 2. Cart badge bounce */
        document.addEventListener('cartUpdated', () => {
            const badge = document.querySelector('.cart-count');
            if (!badge) return;
            badge.classList.remove('bounce');
            void badge.offsetWidth;
            badge.classList.add('bounce');
            badge.addEventListener('animationend', () => badge.classList.remove('bounce'), { once: true });
        });

        /* 3. Add-to-cart success state */
        document.querySelectorAll('.add-cart-btn').forEach(btn => {
            btn.dataset.label = btn.innerHTML;
            btn.addEventListener('cartSuccess', () => {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                setTimeout(() => {
                    btn.innerHTML = '<i class="fas fa-check"></i>';
                    btn.classList.add('btn-success');
                }, 480);
                setTimeout(() => {
                    btn.innerHTML = btn.dataset.label;
                    btn.classList.remove('btn-success');
                    btn.disabled = false;
                }, 1900);
            });
        });

        /* 4. Qty micro-bounce */
        document.querySelectorAll('.qty-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.classList.add('pressing');
                btn.addEventListener('animationend', () => btn.classList.remove('pressing'), { once: true });
            });
        });

    });
    </script>

    @stack('scripts')
</body>
</html>