@extends('layouts.app')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&display=swap" rel="stylesheet">
@endpush

@section('content')
{{-- Banner Carousel --}}
@php
    $validBanners = isset($banners) ? $banners->filter(fn($b) => !empty($b->pc_image_url)) : collect();
@endphp
@if($validBanners->count() > 0)
<section class="relative w-full">
    <div class="swiper banner-swiper">
        <div class="swiper-wrapper">
            @foreach($validBanners->values() as $index => $banner)
            <div class="swiper-slide">
                @if($banner->link_url)
                <a href="{{ $banner->link_url }}">
                @endif
                <picture>
                    @if($banner->mobile_image_url)
                    <source media="(max-width: 768px)" srcset="{{ $banner->mobile_image_url }}">
                    @endif
                    <img src="{{ $banner->pc_image_url }}"
                         alt="{{ $banner->title }}"
                         class="w-full h-auto"
                         {{ $index === 0 ? 'loading=eager fetchpriority=high' : 'loading=lazy' }}>
                </picture>
                @if($banner->link_url)
                </a>
                @endif
            </div>
            @endforeach
        </div>
        @if($validBanners->count() > 1)
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
        @endif
    </div>
</section>
@else
{{-- Hero Section --}}
<section class="hero-gradient overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center min-h-[520px] py-16 lg:py-0">
            {{-- Text Content --}}
            <div class="max-w-xl">
                <p class="text-sm font-semibold text-[#BE185D] tracking-widest uppercase mb-4">{{ __('frontend.home.welcome_label') ?? 'Welcome' }}</p>
                <h1 class="text-4xl md:text-5xl lg:text-[3.5rem] leading-tight mb-6 text-gray-900" style="font-family: 'Playfair Display', serif; font-weight: 700;">
                    {{ __('frontend.home.hero_title_prefix') ?? 'Your Source for' }}<br>
                    <span class="text-[#BE185D] italic">{{ __('frontend.home.hero_title_accent') ?? 'Creative' }}</span> {{ __('frontend.home.hero_title_suffix') ?? 'Wholesale.' }}
                </h1>
                <p class="text-lg text-gray-500 mb-8 leading-relaxed">
                    {{ $site?->meta_description ?? __('frontend.home.default_description') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ lroute('products.index') }}"
                       class="inline-flex items-center justify-center px-8 py-3.5 mod-btn text-base">
                        {{ __('frontend.home.browse_products') }}
                    </a>
                    <a href="{{ lroute('contact') }}"
                       class="inline-flex items-center justify-center px-8 py-3.5 mod-btn-outline text-base">
                        {{ __('frontend.home.contact_us') }}
                    </a>
                </div>
            </div>
            {{-- Decorative Image Area --}}
            <div class="hidden lg:grid grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div class="bg-gray-100 rounded-2xl h-56 overflow-hidden">
                        <div class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200"></div>
                    </div>
                    <div class="bg-gray-100 rounded-2xl h-40 overflow-hidden">
                        <div class="w-full h-full bg-gradient-to-br from-pink-50 to-gray-100"></div>
                    </div>
                </div>
                <div class="space-y-4 pt-8">
                    <div class="bg-gray-100 rounded-2xl h-40 overflow-hidden">
                        <div class="w-full h-full bg-gradient-to-br from-gray-50 to-gray-200"></div>
                    </div>
                    <div class="bg-gray-100 rounded-2xl h-56 overflow-hidden">
                        <div class="w-full h-full bg-gradient-to-br from-gray-100 to-pink-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- Featured Categories --}}
<section class="relative py-16 md:py-24 bg-gray-50/50">
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-12">
            <div>
                <h2 class="text-3xl md:text-4xl text-gray-900 mb-2" style="font-family: 'Playfair Display', serif; font-weight: 700;">
                    {{ __('frontend.home.featured_categories') }}
                </h2>
                <p class="text-gray-400">{{ __('frontend.home.explore_categories') }}</p>
            </div>
            <a href="{{ lroute('products.index') }}"
               class="hidden sm:inline-flex items-center text-sm font-semibold text-[#BE185D] hover:text-[#9D174D] transition gap-1">
                {{ __('frontend.home.view_all_categories') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($featuredCategories as $cat)
            <a href="{{ lroute('category.show', ['slug' => $cat->slug ?: (collect($cat->getTranslations('slug'))->first() ?: 'category')]) }}" class="mod-card group overflow-hidden block">
                <div class="overflow-hidden aspect-[4/3] relative">
                    @if($cat->image_path)
                    <img src="{{ asset('storage/' . $cat->image_path) }}"
                         alt="{{ $cat->name }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                         loading="lazy">
                    @else
                    <div class="w-full h-full bg-gradient-to-br from-gray-50 to-gray-200 flex items-center justify-center">
                        <svg class="w-16 h-16 text-gray-400 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    @endif
                </div>
                <div class="p-5 text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-[#BE185D] transition">
                        {{ $cat->name }}
                    </h3>
                    <p class="text-[#BE185D] text-sm font-semibold inline-flex items-center gap-1 group-hover:underline">
                        {{ __('frontend.product.explore_category_range') ?: '探索精選商品' }} →
                    </p>
                </div>
            </a>
            @empty
            <div class="col-span-3 text-center py-16 text-gray-400">
                {{ __('frontend.home.no_categories') }}
            </div>
            @endforelse
        </div>
    </div>
</section>

{{-- Featured Products --}}
<section class="relative py-16 md:py-24 bg-cover bg-center" style="background-image: url('{{ asset('images/products_bg.png') }}');">
    <div class="absolute inset-0 bg-white/80"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-12">
            <div>
                <h2 class="text-3xl md:text-4xl text-gray-900 mb-2" style="font-family: 'Playfair Display', serif; font-weight: 700;">{{ __('frontend.home.featured_products') }}</h2>
                <p class="text-gray-400">{{ __('frontend.home.explore_popular') }}</p>
            </div>
            <a href="{{ lroute('products.index') }}"
               class="hidden sm:inline-flex items-center text-sm font-semibold text-[#BE185D] hover:text-[#9D174D] transition gap-1">
                {{ __('frontend.home.view_all_products') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($featuredProducts as $product)
            <article class="mod-card group">
                @php $primaryImg = $product->primaryImage ?? $product->images->first(); @endphp
                @if($primaryImg)
                <div class="overflow-hidden aspect-[4/3]">
                    <img src="{{ asset('storage/' . $primaryImg->paths['thumb']) }}"
                         alt="{{ $product->meta_keywords ?? $product->name }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                         loading="lazy">
                </div>
                @else
                <div class="aspect-[4/3] bg-gray-100 flex items-center justify-center">
                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                @endif
                <div class="p-5">
                    <h3 class="text-lg font-semibold text-gray-900 mb-1.5 group-hover:text-[#BE185D] transition">
                        {{ $product->name }}
                    </h3>
                    <p class="text-gray-400 text-sm mb-4 line-clamp-2">
                        {{ Str::limit($product->description, 80) }}
                    </p>
                    <div class="flex items-center justify-between">
                        @if(false)
                        <span class="text-xl font-bold text-[#BE185D]">NT$ {{ number_format($product->price) }}</span>
                        @endif
                        <a href="{{ lroute('products.show', ['id' => $product->id, 'slug' => $product->slug ?: 'product']) }}"
                           class="text-gray-400 hover:text-[#BE185D] transition text-sm font-medium">
                            {{ __('frontend.home.view_details') }} →
                        </a>
                    </div>
                </div>
            </article>
            @empty
            <div class="col-span-3 text-center py-16 text-gray-400">
                {{ __('frontend.home.no_products') }}
            </div>
            @endforelse
        </div>

        <div class="text-center mt-10 sm:hidden">
            <a href="{{ lroute('products.index') }}"
               class="inline-flex items-center px-6 py-3 mod-btn-light rounded-lg font-semibold text-sm">
                {{ __('frontend.home.view_all_products') }}
            </a>
        </div>
    </div>
</section>

{{-- Latest News --}}
<section class="relative py-16 md:py-24 bg-cover bg-center" style="background-image: url('{{ asset('images/news_bg.png') }}');">
    <div class="absolute inset-0 bg-gray-50/85"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-12">
            <div>
                <h2 class="text-3xl md:text-4xl text-gray-900 mb-2" style="font-family: 'Playfair Display', serif; font-weight: 700;">{{ __('frontend.home.latest_news') }}</h2>
                <p class="text-gray-400">{{ __('frontend.home.stay_updated') }}</p>
            </div>
            <a href="{{ lroute('news.index') }}"
               class="hidden sm:inline-flex items-center text-sm font-semibold text-[#BE185D] hover:text-[#9D174D] transition gap-1">
                {{ __('frontend.home.view_all_news') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($latestNews as $news)
            <article class="mod-card group">
                @if($news->image_url)
                <div class="overflow-hidden aspect-[16/10]">
                    <img src="{{ $news->image_url }}"
                         alt="{{ $news->meta_keywords ?? $news->title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                         loading="lazy">
                </div>
                @endif
                <div class="p-5">
                    <time class="text-xs text-[#BE185D] font-semibold tracking-wide" datetime="{{ $news->published_at?->toISOString() }}">
                        {{ $news->published_at?->format('Y/m/d') }}
                    </time>
                    <h3 class="text-lg font-semibold text-gray-900 mt-2 mb-2 group-hover:text-[#BE185D] transition line-clamp-2">
                        {{ $news->title }}
                    </h3>
                    <p class="text-gray-400 text-sm line-clamp-2 mb-4">
                        {{ Str::limit($news->content, 80) }}
                    </p>
                    <a href="{{ lroute('news.show', ['id' => $news->id, 'slug' => $news->slug ?? 'news']) }}"
                       class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-[#BE185D] transition gap-1">
                        {{ __('frontend.home.read_more') }}
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </article>
            @empty
            <div class="col-span-3 text-center py-16 text-gray-400">
                {{ __('frontend.home.no_news') }}
            </div>
            @endforelse
        </div>

        <div class="text-center mt-10 sm:hidden">
            <a href="{{ lroute('news.index') }}"
               class="inline-flex items-center px-6 py-3 mod-btn-light rounded-lg font-semibold text-sm">
                {{ __('frontend.home.view_all_news') }}
            </a>
        </div>
    </div>
</section>

{{-- CTA Section --}}
<section class="relative py-24 md:py-32 bg-cover bg-center overflow-hidden" style="background-image: url('{{ asset('images/cta_bg.png') }}');">
    <div class="absolute inset-0 bg-gradient-to-b from-white/10 to-white/30 backdrop-blur-[2px]"></div>
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="backdrop-blur-md bg-white/75 border border-white/40 shadow-2xl rounded-3xl py-12 px-6 md:px-12 transition hover:shadow-white/20">
            <h2 class="text-3xl md:text-4xl text-gray-900 mb-4" style="font-family: 'Playfair Display', serif; font-weight: 700;">
                {{ __('frontend.home.cta_title') ?? __('frontend.home.contact_us') }}
            </h2>
            <p class="text-gray-600 mb-8 text-lg leading-relaxed max-w-xl mx-auto font-medium">
                {{ $site?->meta_description ?? __('frontend.home.default_description') }}
            </p>
            <a href="{{ lroute('contact') }}"
               class="inline-flex items-center justify-center px-10 py-4 mod-btn text-base hover:scale-105 transition transform duration-300">
                {{ __('frontend.home.contact_us') }}
            </a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var swiperEl = document.querySelector('.banner-swiper');
    if (swiperEl) {
        var slideCount = swiperEl.querySelectorAll('.swiper-slide').length;
        new Swiper('.banner-swiper', {
            loop: slideCount > 1,
            autoplay: slideCount > 1 ? {
                delay: 5000,
                disableOnInteraction: false,
            } : false,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
        });
    }
});
</script>
@endpush

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => $site?->name ?? config('app.name'),
    'url' => url('/'),
    'logo' => $site?->logo_url ?? asset('images/logo.png'),
    'description' => $site?->meta_description ?? '',
    'contactPoint' => [
        '@type' => 'ContactPoint',
        'telephone' => $site?->contact_phone ?? '',
        'email' => $site?->contact_email ?? '',
        'contactType' => 'customer service',
    ],
    'sameAs' => array_values(array_filter($site?->social_links ?? [])),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
