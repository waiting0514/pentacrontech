@extends('layouts.app')

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
@endpush

@php
    $t = fn (string $k) => __('frontend.home.ix.' . $k);
    $arrow = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M2 8h11M9 4l4 4-4 4"/></svg>';
    $catUrl = fn ($cat) => lroute('category.show', ['slug' => $cat->slug ?: (collect($cat->getTranslations('slug'))->first() ?: 'category')]);
    $validBanners = isset($banners) ? $banners->filter(fn ($b) => !empty($b->pc_image_url))->values() : collect();
    $apps = collect(range(1, 6))->map(fn ($i) => [
        'no' => sprintf('%02d', $i),
        'en' => $t("app{$i}_t"),
        'zh' => $t("app{$i}_z"),
        'desc' => $t("app{$i}_d"),
    ])->values();
    $featured = $featuredProducts->first();
    $featuredImg = $featured ? ($featured->primaryImage ?? $featured->images->first()) : null;
@endphp

@section('content')

{{-- ========== HERO ========== --}}
@if($validBanners->count() > 0)
<section class="ix-banner" id="top">
    <div class="swiper banner-swiper">
        <div class="swiper-wrapper">
            @foreach($validBanners as $index => $banner)
            <div class="swiper-slide">
                @if($banner->link_url)<a href="{{ $banner->link_url }}">@endif
                <picture>
                    @if($banner->mobile_image_url)
                    <source media="(max-width: 768px)" srcset="{{ $banner->mobile_image_url }}">
                    @endif
                    <img src="{{ $banner->pc_image_url }}" alt="{{ $banner->title }}" class="w-full h-auto"
                         {{ $index === 0 ? 'loading=eager fetchpriority=high' : 'loading=lazy' }}>
                </picture>
                @if($banner->link_url)</a>@endif
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
<section class="ix-hero ix-dark" id="top">
    <div class="ix-ph" style="position:absolute;inset:0" aria-hidden="true">
        <svg viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" fill="none" style="position:absolute;inset:0;width:100%;height:100%">
            <g stroke="rgba(214,226,238,.34)" stroke-width="1.2">
                <path d="M640 760 H1400"/><rect x="760" y="610" width="560" height="150"/>
                <path d="M760 650 H1320 M790 760 v-150 M1290 760 v-150"/>
                <rect x="1080" y="150" width="200" height="460"/><path d="M1110 150 v460 M1250 150 v460"/>
                <rect x="960" y="210" width="170" height="210"/><path d="M960 260 H1130 M960 380 H1130"/>
                <rect x="1010" y="420" width="70" height="56"/><path d="M1030 476 h30 l-6 62 h-18 z"/>
                <path d="M880 540 h320 v40 h-320 z"/><rect x="860" y="500" width="34" height="110"/><rect x="1186" y="500" width="34" height="110"/>
                <ellipse cx="1040" cy="540" rx="118" ry="22"/><rect x="996" y="508" width="88" height="32"/>
            </g>
            <g stroke="#168BD2" stroke-width="1.4"><circle cx="1045" cy="540" r="34" stroke-dasharray="4 6"/><path d="M1045 492 v-20 M1045 588 v20 M997 540 h-20 M1093 540 h20"/></g>
            <g fill="rgba(214,226,238,.55)" font-family="IBM Plex Mono, monospace" font-size="13" letter-spacing="2">
                <text x="1110" y="104">X — 1,000</text><text x="1356" y="384">Z</text><text x="1160" y="700">C 360°</text><text x="1226" y="530">A ±120°</text><text x="1054" y="600" fill="#168BD2">TCP</text>
            </g>
        </svg>
    </div>
    <div class="ix-hero-scrim"></div>
    <div class="ix-wrap ix-hero-body">
        <div class="ix-hero-copy">
            <p class="ix-eyebrow" style="color:#C3D3E3">{{ $t('hero_eyebrow') }}</p>
            <h1 style="margin:0;display:flex;flex-direction:column;gap:22px">
                <span class="ix-d-xl" lang="en">{{ $t('hero_a') }}<br>{{ $t('hero_b') }}<span style="color:#168BD2">.</span></span>
                <span class="ix-h-zh" style="font-size:clamp(24px,2.6vw,38px);font-weight:500;color:#E3EAF1">{{ $t('hero_sub') }}</span>
            </h1>
            <p class="ix-body-lg" style="color:#A9B6C4;max-width:560px">{{ $site?->meta_description ?: $t('hero_desc') }}</p>
            <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:8px">
                <a href="{{ lroute('products.index') }}" class="ix-btn ix-btn-p">{{ $t('hero_cta1') }} {!! $arrow !!}</a>
                <a href="#solutions" class="ix-btn ix-btn-gd">{{ $t('hero_cta2') }}</a>
            </div>
        </div>
    </div>
    <div style="position:relative;z-index:2;border-top:1px solid rgba(255,255,255,.14)">
        <div class="ix-wrap" style="display:flex;justify-content:space-between;align-items:center;min-height:72px;gap:24px">
            <span class="ix-mono" style="font-size:11px;letter-spacing:.14em;color:#7F8FA1;text-transform:uppercase">{{ $site?->name ?? config('app.name') }}</span>
            <a href="#machines" class="ix-mono" style="display:inline-flex;align-items:center;gap:12px;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#C3D3E3;text-decoration:none;min-height:44px">
                {{ $t('scroll') }}
                <svg width="14" height="22" viewBox="0 0 14 22" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><rect x="1" y="1" width="12" height="20" rx="6"/><path d="M7 5v4" stroke="#168BD2"/></svg>
            </a>
        </div>
    </div>
</section>
@endif

{{-- ========== MACHINES (featured categories) ========== --}}
<section class="ix-sec bg-white" id="machines">
    <div class="ix-wrap">
        <div class="ix-sh">
            <div class="ix-sh-l">
                <p class="ix-eyebrow text-steel"><b>01</b> — {{ $t('m_eyebrow') }}</p>
                <h2 style="margin:0;display:flex;flex-direction:column;gap:18px">
                    <span class="ix-d-lg" lang="en">{{ $t('m_a') }}<br>{{ $t('m_b') }}</span>
                    <span class="ix-h-zh text-steel" style="font-size:clamp(20px,1.8vw,26px)">{{ $t('m_zh') }}</span>
                </h2>
            </div>
            <div class="ix-sh-r">
                <p style="margin:0;font-size:15.5px;line-height:1.75;color:#4A5868">{{ $t('m_desc') }}</p>
                <a href="{{ lroute('products.index') }}" class="ix-alink text-ink">{{ $t('m_all') }} {!! $arrow !!}</a>
            </div>
        </div>

        @if($featuredCategories->isNotEmpty())
        <div class="ix-mgrid">
            @foreach($featuredCategories as $i => $cat)
                @php $cls = ['ix-m-a', 'ix-m-b', 'ix-m-c'][$i] ?? 'ix-m-b'; @endphp
                <a href="{{ $catUrl($cat) }}" class="ix-mcard ix-zoom {{ $cls }}">
                    <div class="ix-ph light" style="background-color:#EEF2F5">
                        @if($cat->image_path)
                        <img src="{{ asset('storage/' . $cat->image_path) }}" alt="{{ $cat->name }}" loading="lazy">
                        @endif
                    </div>
                    <div class="ix-m-meta">
                        <span class="ix-m-idx"><b>{{ sprintf('%02d', $i + 1) }}</b></span>
                        <h3 class="ix-m-name">{{ $cat->name }}</h3>
                        <span class="ix-alink">{{ $t('explore_series') }} {!! $arrow !!}</span>
                    </div>
                </a>
            @endforeach
            <a href="#solutions" class="ix-mcard ix-zoom ix-m-d ix-dark">
                <div class="ix-ph"></div>
                <div class="ix-m-meta">
                    <span class="ix-m-idx" style="color:#A9B6C4"><b style="color:#5AB4EC">{{ sprintf('%02d', $featuredCategories->count() + 1) }}</b>Automation</span>
                    <h3 class="ix-m-name" style="font-size:clamp(28px,2.8vw,42px)">{{ $t('auto_card_t') }}
                        <span class="ix-h-zh" style="display:block;font-size:.5em;font-weight:500;color:#C3D3E3;text-transform:none;margin-top:8px">{{ $t('auto_card_z') }}</span>
                    </h3>
                    <p style="margin:0;font-size:15px;line-height:1.7;color:#A9B6C4;max-width:46ch">{{ $t('auto_card_d') }}</p>
                    <span class="ix-alink text-white">{{ $t('auto_card_l') }} {!! $arrow !!}</span>
                </div>
            </a>
        </div>
        @else
        <p class="text-center py-16 text-muted">{{ __('frontend.home.no_categories') }}</p>
        @endif
    </div>
</section>

{{-- ========== FEATURED MACHINE (latest product) ========== --}}
@if($featured)
<section class="ix-sec bg-lsteel">
    <div class="ix-wrap ix-feat">
        <div class="ix-feat-img">
            <div class="ix-ph light">
                @if($featuredImg)
                <img src="{{ asset('storage/' . $featuredImg->paths['thumb']) }}" alt="{{ $featured->name }}" loading="lazy" style="object-fit:contain">
                @endif
            </div>
        </div>
        <div class="ix-feat-info">
            <p class="ix-eyebrow text-steel"><b>02</b> — {{ $t('feat_eyebrow') }}</p>
            <div style="display:flex;flex-direction:column;gap:14px">
                <h2 class="ix-model">{{ $featured->name }}</h2>
            </div>
            <p style="margin:0;font-size:15.5px;line-height:1.8;color:#4A5868">{{ Str::limit(strip_tags($featured->description), 220) }}</p>
            <div style="display:flex;flex-wrap:wrap;gap:12px">
                <a href="{{ lroute('products.show', ['id' => $featured->id, 'slug' => $featured->slug ?: 'product']) }}" class="ix-btn ix-btn-p">{{ $t('feat_view') }} {!! $arrow !!}</a>
                <a href="{{ lroute('contact') }}" class="ix-btn ix-btn-gl">{{ $t('feat_quote') }}</a>
            </div>
        </div>
    </div>
</section>
@endif

{{-- ========== AUTOMATION ========== --}}
<section class="ix-sec ix-dark" id="solutions" style="background:#082A4A">
    <div class="ix-wrap">
        <div class="ix-sh" style="margin-bottom:clamp(56px,8vw,112px)">
            <div class="ix-sh-l">
                <p class="ix-eyebrow" style="color:#A9B6C4"><b>03</b> — {{ $t('auto_eyebrow') }}</p>
                <h2 style="margin:0;display:flex;flex-direction:column;gap:20px">
                    <span class="ix-d-lg" lang="en">{{ $t('auto_a') }}<br>{{ $t('auto_b') }}<span style="color:#5AB4EC">.</span></span>
                    <span class="ix-h-zh" style="font-size:clamp(20px,1.8vw,26px);color:#C3D3E3">{{ $t('auto_zh') }}</span>
                </h2>
            </div>
            <div class="ix-sh-r"><p class="ix-muted" style="margin:0;font-size:15.5px;line-height:1.8">{{ $t('auto_desc') }}</p></div>
        </div>
        <ol class="ix-rail">
            @foreach(range(1, 6) as $i)
            <li class="ix-step {{ $i === 6 ? 'end' : '' }}">
                <span class="ix-sn">{{ sprintf('%02d', $i) }}</span>
                <span style="font-weight:700;font-size:15px;letter-spacing:.06em;text-transform:uppercase" lang="en">{{ $t("auto_step{$i}") }}</span>
                <span style="font-size:14px;color:#A9B6C4">{{ $t("auto_step{$i}_z") }}</span>
            </li>
            @endforeach
        </ol>
        <div style="display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:2px;margin-top:clamp(48px,6vw,96px)">
            <div class="ix-ph" style="grid-column:span 12;min-height:300px;background-color:#0B3358"></div>
            <div style="grid-column:span 12;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:32px;padding:40px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.13)">
                <div style="display:flex;flex-direction:column;gap:12px;max-width:520px">
                    <span class="ix-mono" style="font-size:11.5px;letter-spacing:.14em;color:#5AB4EC;text-transform:uppercase">{{ $t('auto_scope') }}</span>
                    <p style="margin:0;font-size:22px;font-weight:600;line-height:1.45">{{ $t('auto_scope_t') }}<br><span style="color:#A9B6C4;font-weight:400;font-size:16px">{{ $t('auto_scope_s') }}</span></p>
                </div>
                <a href="{{ lroute('contact') }}" class="ix-btn ix-btn-w">{{ $t('auto_btn') }} {!! $arrow !!}</a>
            </div>
        </div>
    </div>
</section>

{{-- ========== APPLICATIONS ========== --}}
<section class="ix-sec bg-white" id="applications">
    <div class="ix-wrap" x-data="{ sel: 0, apps: {{ Js::from($apps) }} }">
        <div class="ix-sh">
            <div class="ix-sh-l">
                <p class="ix-eyebrow text-steel"><b>04</b> — {{ $t('apps_eyebrow') }}</p>
                <h2 style="margin:0;display:flex;flex-direction:column;gap:18px">
                    <span class="ix-d-lg" lang="en">{{ $t('apps_a') }}<br>{{ $t('apps_b') }}</span>
                    <span class="ix-h-zh text-steel" style="font-size:clamp(20px,1.8vw,26px)">{{ $t('apps_zh') }}</span>
                </h2>
            </div>
        </div>
        <div class="ix-apps">
            <ul class="ix-app-list">
                <template x-for="(a, i) in apps" :key="a.no">
                    <li>
                        <button type="button" class="ix-app-btn" :class="{ on: sel === i }" @click="sel = i" :aria-pressed="(sel === i).toString()">
                            <span class="an" x-text="a.no"></span>
                            <span class="at"><span style="all:unset" lang="en" x-text="a.en"></span><span x-text="a.zh"></span></span>
                            <svg class="aa" width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M2 8h11M9 4l4 4-4 4"/></svg>
                        </button>
                    </li>
                </template>
            </ul>
            <div class="ix-app-stage">
                <div class="ix-ph"></div>
                <div class="ix-app-card" aria-live="polite">
                    <span class="ix-mono" style="font-size:12px;letter-spacing:.14em;color:#5AB4EC" x-text="apps[sel].no + ' / 06'"></span>
                    <h3 style="margin:0;font-weight:800;font-stretch:112%;font-size:clamp(30px,3.4vw,52px);line-height:.95;text-transform:uppercase;letter-spacing:-.02em" lang="en" x-text="apps[sel].en"></h3>
                    <p style="margin:0;font-size:15.5px;line-height:1.75;color:#C3D3E3;max-width:52ch" x-text="apps[sel].desc"></p>
                    <a href="{{ lroute('products.index') }}" class="ix-alink ix-dark" style="align-self:flex-start">{{ $t('apps_more') }} {!! $arrow !!}</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========== ENGINEERING METRICS ========== --}}
<section class="ix-sec bg-off" id="technology">
    <div class="ix-wrap">
        <div class="ix-sh">
            <div class="ix-sh-l">
                <p class="ix-eyebrow text-steel"><b>05</b> — {{ $t('eng_eyebrow') }}</p>
                <h2 style="margin:0;display:flex;flex-direction:column;gap:18px">
                    <span class="ix-d-lg" lang="en">{{ $t('eng_a') }}<br>{{ $t('eng_b') }}</span>
                    <span class="ix-h-zh text-steel" style="font-size:clamp(20px,1.8vw,26px)">{{ $t('eng_zh') }}</span>
                </h2>
            </div>
        </div>
        <div class="ix-stats">
            @foreach(range(1, 3) as $i)
            <div class="ix-stat">
                <span class="ix-sv">{{ $t("stat{$i}_v") }}</span>
                <span class="ix-sl">{{ $t("stat{$i}_l") }}</span>
                <p style="margin:0;font-size:14.5px;line-height:1.7;color:#475569;max-width:30ch">{{ $t("stat{$i}_d") }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ========== COMPANY ========== --}}
<section id="about" class="ix-dark">
    <div class="ix-co">
        <div class="ix-co-img"><div class="ix-ph"></div></div>
        <div class="ix-co-txt">
            <p class="ix-eyebrow" style="color:#A9B6C4"><b>06</b> — {{ $t('co_eyebrow') }}</p>
            <h2 style="margin:0;display:flex;flex-direction:column;gap:18px">
                <span class="ix-d-lg" lang="en" style="font-size:clamp(36px,4.4vw,68px)">{{ $t('co_a') }}<br>{{ $t('co_b') }}<span style="color:#5AB4EC">.</span></span>
                <span class="ix-h-zh" style="font-size:clamp(19px,1.6vw,23px);color:#C3D3E3">{{ $t('co_zh') }}</span>
            </h2>
            <p style="margin:0;font-size:15.5px;line-height:1.85;color:#A9B6C4">{{ $t('co_desc') }}</p>
            <a href="{{ lroute('about') }}" class="ix-btn ix-btn-gd" style="align-self:flex-start">{{ $t('co_btn') }} {!! $arrow !!}</a>
        </div>
    </div>
</section>

{{-- ========== NEWS ========== --}}
@if($latestNews->isNotEmpty())
<section class="ix-sec bg-white" id="news">
    <div class="ix-wrap">
        <div class="ix-sh" style="margin-bottom:48px">
            <div class="ix-sh-l">
                <p class="ix-eyebrow text-steel"><b>07</b> — {{ $t('news_eyebrow') }}</p>
                <h2 style="margin:0;display:flex;flex-direction:column;gap:18px">
                    <span class="ix-d-lg" lang="en">{{ $t('news_t') }}</span>
                    <span class="ix-h-zh text-steel" style="font-size:clamp(20px,1.8vw,26px)">{{ $t('news_zh') }}</span>
                </h2>
            </div>
            <div class="ix-sh-r" style="align-items:flex-end"><a href="{{ lroute('news.index') }}" class="ix-alink text-ink">{{ $t('news_all') }} {!! $arrow !!}</a></div>
        </div>
        <div class="ix-news">
            @php $lead = $latestNews->first(); @endphp
            <a href="{{ lroute('news.show', ['id' => $lead->id, 'slug' => $lead->slug ?? 'news']) }}" class="ix-n-big ix-zoom">
                <div class="ix-ph light">@if($lead->image_url)<img src="{{ $lead->image_url }}" alt="{{ $lead->title }}" loading="lazy">@endif</div>
                <div class="ix-n-meta"><time datetime="{{ $lead->published_at?->toISOString() }}">{{ $lead->published_at?->format('Y.m.d') }}</time></div>
                <h3 class="ix-n-t" style="font-size:clamp(22px,2.2vw,32px)">{{ $lead->title }}</h3>
            </a>
            <div class="ix-n-side">
                @foreach($latestNews->skip(1) as $news)
                <a href="{{ lroute('news.show', ['id' => $news->id, 'slug' => $news->slug ?? 'news']) }}" class="ix-n-row ix-zoom">
                    <div style="display:flex;flex-direction:column;gap:14px">
                        <div class="ix-n-meta"><time datetime="{{ $news->published_at?->toISOString() }}">{{ $news->published_at?->format('Y.m.d') }}</time></div>
                        <h3 class="ix-n-t" style="font-size:18px">{{ $news->title }}</h3>
                    </div>
                    <div class="ix-ph light">@if($news->image_url)<img src="{{ $news->image_url }}" alt="{{ $news->title }}" loading="lazy">@endif</div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

{{-- ========== CTA ========== --}}
<section class="ix-cta ix-dark" id="contact">
    <div class="ix-ph" aria-hidden="true"></div>
    <div class="ix-cta-scrim"></div>
    <div class="ix-wrap ix-cta-in">
        <div style="grid-column:span 8;display:flex;flex-direction:column;gap:28px">
            <p class="ix-eyebrow" style="color:#C3D3E3">{{ $t('cta_eyebrow') }}</p>
            <h2 style="margin:0"><span class="ix-d-xl" lang="en" style="font-size:clamp(40px,6.6vw,104px)">{{ $t('cta_a') }}<br>{{ $t('cta_b') }}<span style="color:#5AB4EC">.</span></span></h2>
            <p class="ix-body-lg" style="color:#C3D3E3;max-width:560px">{{ $t('cta_desc') }}</p>
        </div>
        <div style="grid-column:span 4;display:flex;flex-direction:column;gap:16px;align-items:flex-start">
            <a href="{{ lroute('contact') }}" class="ix-btn ix-btn-w">{{ $t('cta_btn') }} {!! $arrow !!}</a>
            @if($site?->contact_phone || $site?->contact_email)
            <span class="ix-mono" style="font-size:13px;color:#C3D3E3;letter-spacing:.06em">{{ collect([$site?->contact_phone, $site?->contact_email])->filter()->implode(' · ') }}</span>
            @endif
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
            autoplay: slideCount > 1 ? { delay: 5000, disableOnInteraction: false } : false,
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            pagination: { el: '.swiper-pagination', clickable: true },
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
