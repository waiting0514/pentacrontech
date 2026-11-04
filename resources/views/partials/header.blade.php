{{-- Site Header: transparent over the homepage hero, solid navy elsewhere / when scrolled --}}
@php
    $site = $site ?? \App\Models\SiteSetting::current();
    $isHome = str_starts_with((string) request()->route()?->getName(), 'home');
    $navCategories = \App\Models\ProductCategory::where('is_active', true)->orderBy('sort_order')->take(3)->get();

    $is = fn (string ...$p) => request()->is(...$p);
    $nav = [
        ['key' => 'products', 'url' => lroute('products.index'), 'on' => $is('products*', '*/products*', 'category/*', '*/category/*'), 'en' => 'Products'],
        ['key' => 'videos', 'url' => lroute('videos.index'), 'on' => $is('videos*', '*/videos*'), 'en' => 'Videos'],
        ['key' => 'applications', 'url' => lroute('home') . '#applications', 'on' => false, 'en' => 'Applications'],
        ['key' => 'technology', 'url' => lroute('knowledge.index'), 'on' => $is('knowledge*', '*/knowledge*'), 'en' => 'Technology'],
        ['key' => 'about', 'url' => lroute('about'), 'on' => $is('about*', '*/about*'), 'en' => 'About'],
        ['key' => 'news', 'url' => lroute('news.index'), 'on' => $is('news*', '*/news*'), 'en' => 'News'],
    ];
@endphp

<header x-data="{ open: false, sub: false, scrolled: false }"
        x-init="scrolled = window.scrollY > 8"
        @scroll.window.passive="scrolled = window.scrollY > 8"
        @keydown.escape.window="if (open) { open = false; $nextTick(() => $refs.burger.focus()) }"
        x-effect="document.body.style.overflow = open ? 'hidden' : ''"
        class="ix-hdr {{ $isHome ? 'is-over' : 'is-static' }}"
        :class="{ 'is-solid': scrolled }">
    <div class="ix-wrap ix-hdr-row">
        <a class="ix-logo" href="{{ lroute('home') }}" aria-label="{{ $site?->name ?? config('app.name') }}">
            @if($site?->logo_url)
                <img src="{{ $site->logo_url }}" alt="{{ $site->name }}" style="height: 40px; width: auto;">
            @else
                <svg width="30" height="30" viewBox="0 0 30 30" fill="none" aria-hidden="true"><path d="M15 2 27 9v12l-12 7L3 21V9z" stroke="currentColor" stroke-width="1.6"/><path d="M15 9.5 21 13v6l-6 3.5L9 19v-6z" fill="#168BD2"/></svg>
                <span>{{ $site?->name ?? config('app.name') }}</span>
            @endif
        </a>

        <nav class="ix-nav" aria-label="{{ __('frontend.nav.main') }}">
            @foreach($nav as $item)
                <a href="{{ $item['url'] }}" @class(['on' => $item['on']]) @if($item['on']) aria-current="page" @endif>{{ __('frontend.nav.' . $item['key']) }}</a>
            @endforeach
        </nav>

        <div class="ix-hdr-r">
            <x-lang-switcher />
            <a href="{{ lroute('contact') }}" class="ix-btn ix-btn-p ix-btn-sm">{{ __('frontend.nav.quote') }}</a>
            <button type="button" class="ix-burger" x-ref="burger" @click="open = true; sub = false"
                    aria-label="{{ __('frontend.nav.open_menu') }}" :aria-expanded="open.toString()" aria-controls="ix-drawer">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 6h16M2 14h16"/></svg>
            </button>
        </div>
    </div>

    {{-- Mobile drawer --}}
    <div id="ix-drawer" class="ix-drawer" x-show="open" x-cloak x-transition.opacity role="dialog" aria-modal="true" aria-label="{{ __('frontend.nav.main') }}">
        <div style="height:72px;display:flex;align-items:center;justify-content:space-between;padding:0 12px 0 20px;border-bottom:1px solid #1C3550;flex:none">
            <span class="ix-logo" style="font-size:17px">
                <svg width="26" height="26" viewBox="0 0 30 30" fill="none" aria-hidden="true"><path d="M15 2 27 9v12l-12 7L3 21V9z" stroke="currentColor" stroke-width="1.6"/><path d="M15 9.5 21 13v6l-6 3.5L9 19v-6z" fill="#168BD2"/></svg>
                {{ $site?->name ?? config('app.name') }}
            </span>
            <button type="button" @click="open = false; $nextTick(() => $refs.burger.focus())" aria-label="{{ __('frontend.nav.close_menu') }}"
                    style="width:48px;height:48px;border:1px solid #2A4560;background:transparent;color:#fff;display:inline-flex;align-items:center;justify-content:center;border-radius:2px">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 3l12 12M15 3 3 15"/></svg>
            </button>
        </div>

        <nav style="flex:1;overflow-y:auto" aria-label="{{ __('frontend.nav.main') }}">
            <button type="button" class="ix-drawer-it" @click="sub = !sub" :aria-expanded="sub.toString()" style="box-shadow: inset 3px 0 0 #168BD2">
                <span>{{ __('frontend.nav.products') }}<span class="en">Products</span></span>
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="#5AB4EC" stroke-width="1.5" :style="sub ? 'transform: rotate(180deg)' : ''" aria-hidden="true"><path d="M3 5l4 4 4-4"/></svg>
            </button>
            <div class="ix-drawer-sub" x-show="sub" x-cloak style="background:#0A2238">
                @foreach($navCategories as $cat)
                    <a href="{{ lroute('category.show', ['slug' => $cat->slug ?: (collect($cat->getTranslations('slug'))->first() ?: 'category')]) }}">{{ $cat->name }}</a>
                @endforeach
                <a href="{{ lroute('products.index') }}" style="color:#5AB4EC">{{ __('frontend.nav.all_products') }} →</a>
            </div>
            @foreach(array_slice($nav, 1) as $item)
                <a href="{{ $item['url'] }}" class="ix-drawer-it" @click="open = false"><span>{{ __('frontend.nav.' . $item['key']) }}<span class="en">{{ $item['en'] }}</span></span></a>
            @endforeach
        </nav>

        <div style="padding:20px;border-top:1px solid #1C3550;display:flex;flex-direction:column;gap:16px;flex:none">
            <div style="display:flex;justify-content:space-between;align-items:center">
                <x-lang-switcher />
                @auth
                    <a href="{{ route('account.show') }}" style="color:#A9B6C4;font-size:14px;text-decoration:none;padding:12px 0">{{ __('frontend.account.title') }}</a>
                @else
                    <a href="{{ route('login') }}" style="color:#A9B6C4;font-size:14px;text-decoration:none;padding:12px 0">{{ __('frontend.auth.login') }}</a>
                @endauth
            </div>
            <a href="{{ lroute('contact') }}" class="ix-btn ix-btn-p" style="min-height:56px;font-size:16px">
                {{ __('frontend.nav.quote') }}
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M2 8h11M9 4l4 4-4 4"/></svg>
            </a>
        </div>
    </div>
</header>
