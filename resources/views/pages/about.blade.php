@extends('layouts.app')

@push('head')
<style>
    /* ===== About Page Scoped Styles (Industrial Blue) ===== */
    .about-serif { font-family: var(--font-display); font-weight: 800; font-stretch: 108%; text-transform: uppercase; letter-spacing: -.01em; }
    .about-sans { font-family: var(--font-sans); }

    /* Scroll reveal */
    .reveal {
        opacity: 0;
        transform: translateY(24px);
        transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1),
                    transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: 0.1s; }
    .reveal-delay-2 { transition-delay: 0.2s; }
    .reveal-delay-3 { transition-delay: 0.3s; }
    .reveal-delay-4 { transition-delay: 0.4s; }

    /* Hero parallax image */
    .about-hero {
        position: relative;
        min-height: 75vh;
        display: flex;
        align-items: center;
        overflow: hidden;
        background: var(--color-navy);
        padding: 96px 0 64px;
    }
    .about-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(7,26,43,.95) 0%, rgba(7,26,43,.75) 45%, rgba(7,26,43,.3) 100%);
        z-index: 1;
    }
    .about-hero-img {
        position: absolute;
        inset: -10%;
        width: 120%;
        max-width: none;
        height: 120%;
        object-fit: cover;
        will-change: transform;
    }

    /* Editorial quote block */
    .editorial-quote {
        position: relative;
        padding-left: 1.5rem;
        border-left: 2px solid var(--color-eblue);
    }

    /* Values card */
    .value-card {
        position: relative;
        border-radius: 2px;
        transition: border-color .25s;
    }
    .value-card::before {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 2px;
        background: var(--color-pblue);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .value-card:hover { border-color: var(--color-eblue); }
    .value-card:hover::before { transform: scaleX(1); }
    .value-icon { width: 48px; height: 48px; background: var(--color-eblue); border-radius: 2px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px; }

    /* Image overlay */
    .img-overlay { position: relative; overflow: hidden; border-radius: 2px; }
    .img-overlay::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 50%, rgba(7,26,43,0.25) 100%);
        pointer-events: none;
    }

    /* Why-us feature box */
    .feature-ring { border: 1px solid var(--color-line); border-radius: 2px; transition: border-color .25s; }
    .feature-ring:hover { border-color: var(--color-eblue); }
    .feature-ico { width: 56px; height: 56px; background: #fff; border: 1px solid var(--color-line); border-radius: 2px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px; }

    /* CTA section */
    .cta-mesh {
        background:
            radial-gradient(ellipse at 20% 50%, rgba(22,139,210,0.18) 0%, transparent 55%),
            var(--color-deep);
    }
</style>
@endpush

@section('content')
{{-- Breadcrumb --}}
<nav class="bg-white border-b border-line py-3">
    <div class="ix-wrap">
        <ol class="flex items-center space-x-2 text-sm font-mono">
            <li><a href="{{ lroute('home') }}" class="text-steel hover:text-eblue transition">{{ __('frontend.common.home') }}</a></li>
            <li class="text-line">/</li>
            <li class="text-ink font-medium">{{ __('frontend.about.title') }}</li>
        </ol>
    </div>
</nav>

{{-- ============================================ --}}
{{-- HERO --}}
{{-- ============================================ --}}
<section class="about-hero">
    <img src="{{ asset('images/about-hero.png') }}"
         alt="" class="about-hero-img" id="heroImg" loading="eager">
    <div class="relative z-10 ix-wrap w-full ix-dark">
        <div class="max-w-3xl">
            <p class="ix-eyebrow mb-6 reveal reveal-delay-1">
                <b>{{ __('frontend.about.story_label') }}</b>
            </p>
            <h1 class="ix-d-lg mb-8 reveal reveal-delay-2">
                {{ __('frontend.about.title') }}
            </h1>
            <p class="ix-body-lg ix-muted max-w-xl reveal reveal-delay-3">
                {{ __('frontend.about.hero_subtitle') }}
            </p>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- STORY --}}
{{-- ============================================ --}}
<section class="ix-sec bg-white relative overflow-hidden">
    <div class="ix-wrap relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-20 items-center">
            {{-- Text column --}}
            <div class="lg:col-span-5">
                <p class="ix-eyebrow mb-6 reveal">
                    <b>{{ __('frontend.about.story_label') }}</b>
                </p>
                <h2 class="ix-d-md mb-8 reveal reveal-delay-1">
                    {{ __('frontend.about.story_title') }}
                </h2>
                <div class="space-y-5 text-steel leading-[1.8] reveal reveal-delay-2">
                    <p>{{ __('frontend.about.story_p1') }}</p>
                    <p>{{ __('frontend.about.story_p2') }}</p>
                </div>
                <div class="editorial-quote mt-8 reveal reveal-delay-3">
                    <p class="text-lg text-ink font-medium leading-relaxed">
                        {{ __('frontend.about.story_p3') }}
                    </p>
                </div>
            </div>

            {{-- Image mosaic column --}}
            <div class="lg:col-span-7 reveal reveal-delay-2">
                <div class="grid grid-cols-12 grid-rows-6 gap-4 h-[520px] md:h-[600px]">
                    <div class="col-span-7 row-span-6 img-overlay">
                        <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=800&q=80"
                             alt="{{ __('frontend.about.story_label') }}"
                             class="w-full h-full object-cover" loading="lazy">
                    </div>
                    <div class="col-span-5 row-span-3 img-overlay">
                        <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=600&q=80"
                             alt="{{ __('frontend.about.story_label') }}"
                             class="w-full h-full object-cover" loading="lazy">
                    </div>
                    <div class="col-span-5 row-span-3 img-overlay">
                        <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=600&q=80"
                             alt="{{ __('frontend.about.story_label') }}"
                             class="w-full h-full object-cover" loading="lazy">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- ============================================ --}}
{{-- MISSION / VISION / VALUES --}}
{{-- ============================================ --}}
<section class="ix-sec bg-off">
    <div class="ix-wrap">
        <div class="mb-16 reveal">
            <p class="ix-eyebrow mb-6"><b>{{ __('frontend.about.values_title') }}</b></p>
            <h2 class="ix-d-md mb-5">
                {{ __('frontend.about.values_title') }}
            </h2>
            <p class="text-steel max-w-lg leading-relaxed">{{ __('frontend.about.values_subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
            {{-- Mission --}}
            <div class="value-card bg-white border border-line p-10 overflow-hidden reveal reveal-delay-1">
                <div class="font-mono text-sm tracking-[.14em] text-eblue mb-6 select-none">01</div>
                <div class="value-icon">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="ix-h-zh text-xl text-ink mb-3">{{ __('frontend.about.mission_title') }}</h3>
                <p class="text-steel leading-relaxed text-[0.95rem]">{{ __('frontend.about.mission_desc') }}</p>
            </div>

            {{-- Vision --}}
            <div class="value-card bg-white border border-line p-10 overflow-hidden reveal reveal-delay-2">
                <div class="font-mono text-sm tracking-[.14em] text-eblue mb-6 select-none">02</div>
                <div class="value-icon">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <h3 class="ix-h-zh text-xl text-ink mb-3">{{ __('frontend.about.vision_title') }}</h3>
                <p class="text-steel leading-relaxed text-[0.95rem]">{{ __('frontend.about.vision_desc') }}</p>
            </div>

            {{-- Core Values --}}
            <div class="value-card bg-white border border-line p-10 overflow-hidden reveal reveal-delay-3">
                <div class="font-mono text-sm tracking-[.14em] text-eblue mb-6 select-none">03</div>
                <div class="value-icon">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <h3 class="ix-h-zh text-xl text-ink mb-3">{{ __('frontend.about.value_title') }}</h3>
                <p class="text-steel leading-relaxed text-[0.95rem]">{{ __('frontend.about.value_desc') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- TEAM --}}
{{-- ============================================ --}}
<section class="relative bg-lsteel overflow-hidden">
    <div class="max-w-[1600px] mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 min-h-[600px]">
            {{-- Image side --}}
            <div class="relative h-80 lg:h-auto overflow-hidden reveal">
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1000&q=80"
                     alt="{{ __('frontend.about.team_title') }}"
                     class="absolute inset-0 w-full h-full object-cover" loading="lazy">
            </div>

            {{-- Text side --}}
            <div class="relative z-10 flex items-center px-6 sm:px-10 lg:px-16 xl:px-20 py-16 lg:py-24">
                <div>
                    <p class="ix-eyebrow mb-6 reveal reveal-delay-1">
                        <b>{{ __('frontend.about.team_label') }}</b>
                    </p>
                    <h2 class="ix-d-md mb-8 reveal reveal-delay-2">
                        {{ __('frontend.about.team_title') }}
                    </h2>
                    <div class="space-y-5 text-steel leading-[1.8] reveal reveal-delay-3">
                        <p>{{ __('frontend.about.team_p1') }}</p>
                        <p>{{ __('frontend.about.team_p2') }}</p>
                    </div>
                    <a href="{{ lroute('contact') }}" class="ix-btn ix-btn-p mt-10 reveal reveal-delay-4">
                        {{ __('frontend.about.team_cta') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- WHY CHOOSE US --}}
{{-- ============================================ --}}
<section class="ix-sec bg-white">
    <div class="ix-wrap">
        <div class="mb-16 reveal">
            <p class="ix-eyebrow mb-6"><b>{{ __('frontend.about.why_title') }}</b></p>
            <h2 class="ix-d-md">
                {{ __('frontend.about.why_title') }}
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
            $features = [
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
                    'title' => __('frontend.about.why_quality'),
                    'desc' => __('frontend.about.why_quality_desc'),
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    'title' => __('frontend.about.why_fast'),
                    'desc' => __('frontend.about.why_fast_desc'),
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
                    'title' => __('frontend.about.why_service'),
                    'desc' => __('frontend.about.why_service_desc'),
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                    'title' => __('frontend.about.why_global'),
                    'desc' => __('frontend.about.why_global_desc'),
                ],
            ];
            @endphp

            @foreach($features as $i => $feat)
            <div class="feature-ring bg-off p-8 reveal reveal-delay-{{ $i + 1 }}">
                <div class="feature-ico">
                    <svg class="w-7 h-7 text-eblue" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $feat['icon'] !!}</svg>
                </div>
                <h3 class="ix-h-zh text-lg text-ink mb-2">{{ $feat['title'] }}</h3>
                <p class="text-sm text-steel leading-relaxed">{{ $feat['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- CTA --}}
{{-- ============================================ --}}
<section class="cta-mesh ix-dark py-24 md:py-32 relative overflow-hidden">
    <div class="ix-wrap relative z-10">
        <div class="max-w-3xl">
            <p class="ix-eyebrow mb-6 reveal"><b>{{ __('frontend.about.cta_title') }}</b></p>
            <h2 class="ix-d-lg mb-6 reveal reveal-delay-1">
                {{ __('frontend.about.cta_title') }}
            </h2>
            <p class="ix-body-lg ix-muted mb-10 max-w-xl reveal reveal-delay-2">
                {{ __('frontend.about.cta_desc') }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 reveal reveal-delay-3">
                <a href="{{ lroute('products.index') }}" class="ix-btn ix-btn-w">
                    {{ __('frontend.home.browse_products') }}
                </a>
                <a href="{{ lroute('contact') }}" class="ix-btn ix-btn-gd">
                    {{ __('frontend.home.contact_us') }}
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Scroll reveal with IntersectionObserver
    const reveals = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
        reveals.forEach(el => observer.observe(el));
    } else {
        reveals.forEach(el => el.classList.add('visible'));
    }

    // Subtle parallax on hero image
    const heroImg = document.getElementById('heroImg');
    if (heroImg && window.innerWidth > 768) {
        let ticking = false;
        window.addEventListener('scroll', function () {
            if (!ticking) {
                requestAnimationFrame(function () {
                    const scrolled = window.pageYOffset;
                    const heroH = heroImg.parentElement.offsetHeight;
                    if (scrolled < heroH) {
                        heroImg.style.transform = 'translateY(' + (scrolled * 0.25) + 'px) scale(1.05)';
                    }
                    ticking = false;
                });
                ticking = true;
            }
        });
    }
});
</script>
@endpush

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'AboutPage',
    'name' => __('frontend.about.title'),
    'description' => __('frontend.about.meta_description'),
    'url' => url()->current(),
    'mainEntity' => [
        '@type' => 'Organization',
        'name' => $site?->name ?? config('app.name'),
        'url' => url('/'),
        'logo' => $site?->logo_url ?? asset('images/logo.png'),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => __('frontend.common.home'), 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => __('frontend.about.title')],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
@endpush
