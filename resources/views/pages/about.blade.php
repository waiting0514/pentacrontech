@extends('layouts.app')

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,700;0,900;1,400;1,500;1,700&family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
<style>
    /* ===== About Page Scoped Styles ===== */
    .about-serif { font-family: 'Playfair Display', Georgia, serif; }
    .about-sans { font-family: 'DM Sans', 'Inter', system-ui, sans-serif; }

    /* Scroll reveal */
    .reveal {
        opacity: 0;
        transform: translateY(40px);
        transition: opacity 0.9s cubic-bezier(0.16, 1, 0.3, 1),
                    transform 0.9s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .reveal.visible {
        opacity: 1;
        transform: translateY(0);
    }
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
        background: #0c0a09;
    }
    .about-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(12,10,9,0.3) 0%, rgba(12,10,9,0.7) 100%);
        z-index: 1;
    }
    .about-hero-img {
        position: absolute;
        inset: -10%;
        width: 120%;
        height: 120%;
        object-fit: cover;
        will-change: transform;
    }

    /* Decorative line */
    .line-accent {
        width: 60px;
        height: 2px;
        background: #BE185D;
        display: block;
    }

    /* Stat counter */
    .stat-number {
        font-family: 'Playfair Display', serif;
        font-weight: 900;
        font-style: italic;
        line-height: 1;
        background: linear-gradient(135deg, #BE185D 0%, #9D174D 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Editorial quote block */
    .editorial-quote {
        position: relative;
        padding-left: 2rem;
        border-left: 3px solid #BE185D;
    }
    .editorial-quote::before {
        content: '\201C';
        font-family: 'Playfair Display', serif;
        font-size: 5rem;
        line-height: 0;
        position: absolute;
        top: 2.5rem;
        left: -0.25rem;
        color: #BE185D;
        opacity: 0.15;
    }

    /* Values card hover lift */
    .value-card {
        position: relative;
        transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .value-card::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #BE185D, #F472B6);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .value-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.1);
    }
    .value-card:hover::before {
        transform: scaleX(1);
    }

    /* Grain overlay for textured sections */
    .grain::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0.03;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
        pointer-events: none;
        z-index: 0;
    }

    /* Image overlay gradient */
    .img-overlay {
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
    }
    .img-overlay::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 50%, rgba(12,10,9,0.25) 100%);
        pointer-events: none;
        border-radius: 1rem;
    }

    /* Why-us feature ring */
    .feature-ring {
        position: relative;
    }
    .feature-ring::before {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 1rem;
        background: linear-gradient(135deg, #BE185D, #F9A8D4, #BE185D);
        opacity: 0;
        transition: opacity 0.4s ease;
        z-index: -1;
    }
    .feature-ring:hover::before {
        opacity: 1;
    }

    /* CTA section mesh */
    .cta-mesh {
        background:
            radial-gradient(ellipse at 20% 50%, rgba(190,24,93,0.15) 0%, transparent 50%),
            radial-gradient(ellipse at 80% 50%, rgba(157,23,77,0.1) 0%, transparent 50%),
            #0c0a09;
    }
</style>
@endpush

@section('content')
{{-- Breadcrumb --}}
<nav class="bg-white border-b border-gray-100 py-3">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <ol class="flex items-center space-x-2 text-sm about-sans">
            <li><a href="{{ lroute('home') }}" class="text-gray-400 hover:text-[#BE185D] transition">{{ __('frontend.common.home') }}</a></li>
            <li class="text-gray-300">/</li>
            <li class="text-gray-900 font-medium">{{ __('frontend.about.title') }}</li>
        </ol>
    </div>
</nav>

{{-- ============================================ --}}
{{-- HERO — Cinematic full-bleed --}}
{{-- ============================================ --}}
<section class="about-hero">
    <img src="{{ asset('images/about-hero.png') }}"
         alt="" class="about-hero-img" id="heroImg" loading="eager">
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="max-w-3xl">
            <span class="line-accent mb-8 reveal"></span>
            <p class="about-sans text-[#F9A8D4] text-sm font-medium tracking-[0.25em] uppercase mb-6 reveal reveal-delay-1">
                {{ __('frontend.about.story_label') }}
            </p>
            <h1 class="about-serif text-4xl sm:text-5xl md:text-6xl lg:text-7xl text-white leading-[1.1] mb-8 reveal reveal-delay-2">
                {{ __('frontend.about.title') }}
            </h1>
            <p class="about-sans text-lg md:text-xl text-gray-300 leading-relaxed max-w-xl reveal reveal-delay-3">
                {{ __('frontend.about.hero_subtitle') }}
            </p>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- STORY — Asymmetric editorial layout --}}
{{-- ============================================ --}}
<section class="py-24 md:py-36 bg-white relative overflow-hidden">
    {{-- Decorative background circle --}}
    <div class="absolute -right-32 top-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-gradient-to-br from-pink-50 to-transparent opacity-60 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-20 items-center">
            {{-- Text column --}}
            <div class="lg:col-span-5">
                <span class="line-accent mb-6 reveal"></span>
                <p class="about-sans text-[#BE185D] text-xs font-medium tracking-[0.25em] uppercase mb-4 reveal">
                    {{ __('frontend.about.story_label') }}
                </p>
                <h2 class="about-serif text-3xl md:text-4xl lg:text-[2.75rem] text-gray-900 leading-[1.15] mb-8 reveal reveal-delay-1">
                    {{ __('frontend.about.story_title') }}
                </h2>
                <div class="about-sans space-y-5 text-gray-500 leading-[1.8] reveal reveal-delay-2">
                    <p>{{ __('frontend.about.story_p1') }}</p>
                    <p>{{ __('frontend.about.story_p2') }}</p>
                </div>
                <div class="editorial-quote mt-8 reveal reveal-delay-3">
                    <p class="about-serif text-lg italic text-gray-700 leading-relaxed">
                        {{ __('frontend.about.story_p3') }}
                    </p>
                </div>
            </div>

            {{-- Image mosaic column --}}
            <div class="lg:col-span-7 reveal reveal-delay-2">
                <div class="grid grid-cols-12 grid-rows-6 gap-4 h-[520px] md:h-[600px]">
                    {{-- Large primary image --}}
                    <div class="col-span-7 row-span-6 img-overlay">
                        <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=800&q=80"
                             alt="{{ __('frontend.about.story_label') }}"
                             class="w-full h-full object-cover rounded-2xl" loading="lazy">
                    </div>
                    {{-- Top right --}}
                    <div class="col-span-5 row-span-3 img-overlay">
                        <img src="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=600&q=80"
                             alt="{{ __('frontend.about.story_label') }}"
                             class="w-full h-full object-cover rounded-2xl" loading="lazy">
                    </div>
                    {{-- Bottom right --}}
                    <div class="col-span-5 row-span-3 img-overlay">
                        <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?w=600&q=80"
                             alt="{{ __('frontend.about.story_label') }}"
                             class="w-full h-full object-cover rounded-2xl" loading="lazy">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- ============================================ --}}
{{-- MISSION / VISION / VALUES — Editorial cards --}}
{{-- ============================================ --}}
<section class="py-24 md:py-36 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-20 reveal">
            <span class="line-accent mx-auto mb-6"></span>
            <h2 class="about-serif text-3xl md:text-4xl lg:text-5xl text-gray-900 leading-tight mb-5">
                {{ __('frontend.about.values_title') }}
            </h2>
            <p class="about-sans text-gray-400 max-w-lg mx-auto leading-relaxed">{{ __('frontend.about.values_subtitle') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-10">
            {{-- Mission --}}
            <div class="value-card bg-white rounded-2xl border border-gray-100 p-10 overflow-hidden reveal reveal-delay-1">
                <div class="about-serif text-6xl font-bold text-gray-100 leading-none mb-6 select-none">01</div>
                <div class="w-12 h-12 bg-gradient-to-br from-[#BE185D] to-[#9D174D] rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-pink-200/40">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="about-serif text-xl font-bold text-gray-900 mb-3">{{ __('frontend.about.mission_title') }}</h3>
                <p class="about-sans text-gray-500 leading-relaxed text-[0.95rem]">{{ __('frontend.about.mission_desc') }}</p>
            </div>

            {{-- Vision --}}
            <div class="value-card bg-white rounded-2xl border border-gray-100 p-10 overflow-hidden reveal reveal-delay-2">
                <div class="about-serif text-6xl font-bold text-gray-100 leading-none mb-6 select-none">02</div>
                <div class="w-12 h-12 bg-gradient-to-br from-[#BE185D] to-[#9D174D] rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-pink-200/40">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <h3 class="about-serif text-xl font-bold text-gray-900 mb-3">{{ __('frontend.about.vision_title') }}</h3>
                <p class="about-sans text-gray-500 leading-relaxed text-[0.95rem]">{{ __('frontend.about.vision_desc') }}</p>
            </div>

            {{-- Core Values --}}
            <div class="value-card bg-white rounded-2xl border border-gray-100 p-10 overflow-hidden reveal reveal-delay-3">
                <div class="about-serif text-6xl font-bold text-gray-100 leading-none mb-6 select-none">03</div>
                <div class="w-12 h-12 bg-gradient-to-br from-[#BE185D] to-[#9D174D] rounded-xl flex items-center justify-center mb-6 shadow-lg shadow-pink-200/40">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <h3 class="about-serif text-xl font-bold text-gray-900 mb-3">{{ __('frontend.about.value_title') }}</h3>
                <p class="about-sans text-gray-500 leading-relaxed text-[0.95rem]">{{ __('frontend.about.value_desc') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- TEAM — Cinematic split layout --}}
{{-- ============================================ --}}
<section class="relative bg-[#faf9f7] grain overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 min-h-[600px]">
            {{-- Image side --}}
            <div class="relative h-80 lg:h-auto overflow-hidden reveal">
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1000&q=80"
                     alt="{{ __('frontend.about.team_title') }}"
                     class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-r from-transparent to-[#faf9f7]/20 hidden lg:block"></div>
            </div>

            {{-- Text side --}}
            <div class="relative z-10 flex items-center px-6 sm:px-10 lg:px-16 xl:px-20 py-16 lg:py-24">
                <div>
                    <span class="line-accent mb-6 reveal"></span>
                    <p class="about-sans text-[#BE185D] text-xs font-medium tracking-[0.25em] uppercase mb-4 reveal reveal-delay-1">
                        {{ __('frontend.about.team_label') }}
                    </p>
                    <h2 class="about-serif text-3xl md:text-4xl text-gray-900 leading-[1.15] mb-8 reveal reveal-delay-2">
                        {{ __('frontend.about.team_title') }}
                    </h2>
                    <div class="about-sans space-y-5 text-gray-500 leading-[1.8] reveal reveal-delay-3">
                        <p>{{ __('frontend.about.team_p1') }}</p>
                        <p>{{ __('frontend.about.team_p2') }}</p>
                    </div>
                    <a href="{{ lroute('contact') }}"
                       class="inline-flex items-center gap-2 mt-10 px-8 py-4 bg-[#BE185D] text-white font-semibold rounded-xl hover:bg-[#9D174D] transition-all duration-300 hover:shadow-lg hover:shadow-pink-200/40 about-sans text-sm reveal reveal-delay-4">
                        {{ __('frontend.about.team_cta') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- WHY CHOOSE US — Feature grid with rings --}}
{{-- ============================================ --}}
<section class="py-24 md:py-36 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-20 reveal">
            <h2 class="about-serif text-3xl md:text-4xl lg:text-5xl text-gray-900 leading-tight">
                {{ __('frontend.about.why_title') }}
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
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
            <div class="text-center reveal reveal-delay-{{ $i + 1 }}">
                <div class="feature-ring w-16 h-16 bg-[#faf9f7] rounded-xl flex items-center justify-center mx-auto mb-6 transition-all duration-300">
                    <svg class="w-7 h-7 text-[#BE185D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $feat['icon'] !!}</svg>
                </div>
                <h3 class="about-serif text-lg font-bold text-gray-900 mb-2">{{ $feat['title'] }}</h3>
                <p class="about-sans text-sm text-gray-400 leading-relaxed">{{ $feat['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- CTA — Dark cinematic with mesh gradient --}}
{{-- ============================================ --}}
<section class="cta-mesh py-24 md:py-32 text-center relative overflow-hidden">
    {{-- Decorative rings --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] border border-white/5 rounded-full pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] border border-white/5 rounded-full pointer-events-none"></div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <span class="line-accent mx-auto mb-8 reveal"></span>
        <h2 class="about-serif text-3xl md:text-4xl lg:text-5xl text-white leading-tight mb-6 reveal reveal-delay-1">
            {{ __('frontend.about.cta_title') }}
        </h2>
        <p class="about-sans text-gray-400 mb-10 text-lg leading-relaxed max-w-xl mx-auto reveal reveal-delay-2">
            {{ __('frontend.about.cta_desc') }}
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center reveal reveal-delay-3">
            <a href="{{ lroute('products.index') }}"
               class="inline-flex items-center justify-center py-4 bg-[#BE185D] text-white font-semibold rounded-xl hover:bg-[#9D174D] transition-all duration-300 hover:shadow-lg hover:shadow-pink-900/30 about-sans" style="padding-left:3.5rem;padding-right:3.5rem;">
                {{ __('frontend.home.browse_products') }}
            </a>
            <a href="{{ lroute('contact') }}"
               class="inline-flex items-center justify-center py-4 border border-white/20 text-white font-semibold rounded-xl hover:bg-white/10 hover:border-white/40 transition-all duration-300 about-sans backdrop-blur-sm" style="padding-left:3.5rem;padding-right:3.5rem;">
                {{ __('frontend.home.contact_us') }}
            </a>
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
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => __('frontend.common.home'), 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => __('frontend.about.title')],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
