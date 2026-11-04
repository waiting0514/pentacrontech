{{-- Site Footer --}}
@php
    $site = $site ?? \App\Models\SiteSetting::current();
    $socialLinks = array_filter($site?->social_links ?? []);
    $socialLabels = ['facebook' => 'Facebook', 'instagram' => 'Instagram', 'youtube' => 'YouTube', 'line' => 'LINE', 'linkedin' => 'LinkedIn'];
@endphp

<footer class="ix-ft">
    <div class="ix-wrap">
        <div class="ix-ft-top">
            <div style="grid-column: span 6; display: flex; flex-direction: column; gap: 20px">
                <a class="ix-logo" href="{{ lroute('home') }}">
                    <svg width="30" height="30" viewBox="0 0 30 30" fill="none" aria-hidden="true"><path d="M15 2 27 9v12l-12 7L3 21V9z" stroke="currentColor" stroke-width="1.6"/><path d="M15 9.5 21 13v6l-6 3.5L9 19v-6z" fill="#168BD2"/></svg>
                    <span>{{ $site?->name ?? config('app.name') }}</span>
                </a>
                <p style="margin:0;font-size:15px;line-height:1.8;color:#93A3B5;max-width:44ch">
                    {{ $site?->meta_description ?: __('frontend.footer.tagline') }}
                </p>
            </div>
            <div style="grid-column: span 6; display: flex; flex-direction: column; gap: 10px; font-size: 14.5px; line-height: 1.7; color: #93A3B5">
                <span class="ix-mono" style="font-size:11.5px;letter-spacing:.14em;color:#fff;text-transform:uppercase;margin-bottom:6px">{{ __('frontend.footer.contact_info') }}</span>
                @if($site?->contact_address)<span>{!! $site->contact_address !!}</span>@endif
                @if($site?->contact_phone)<span>T <a href="tel:{{ preg_replace('/[^0-9+]/', '', $site->contact_phone) }}" style="color:inherit">{{ $site->contact_phone }}</a></span>@endif
                @if($site?->contact_email)<span>E <a href="mailto:{{ $site->contact_email }}" style="color:inherit">{{ $site->contact_email }}</a></span>@endif
            </div>
        </div>

        <div class="ix-ft-cols">
            <div>
                <h4>{{ __('frontend.nav.products') }}</h4>
                <ul>
                    @foreach(\App\Models\ProductCategory::where('is_active', true)->orderBy('sort_order')->take(3)->get() as $cat)
                        <li><a href="{{ lroute('category.show', ['slug' => $cat->slug ?: (collect($cat->getTranslations('slug'))->first() ?: 'category')]) }}">{{ $cat->name }}</a></li>
                    @endforeach
                    <li><a href="{{ lroute('products.index') }}">{{ __('frontend.nav.all_products') }}</a></li>
                </ul>
            </div>
            <div>
                <h4>{{ __('frontend.nav.videos') }}</h4>
                <ul>
                    @foreach(\App\Models\Video::active()->ordered()->take(3)->get() as $footerVideo)
                        <li><a href="{{ $footerVideo->url }}">{{ $footerVideo->title }}</a></li>
                    @endforeach
                    <li><a href="{{ lroute('videos.index') }}">{{ __('frontend.nav.all_videos') }}</a></li>
                </ul>
            </div>
            <div>
                <h4>{{ __('frontend.nav.applications') }}</h4>
                <ul>
                    @foreach(['app1', 'app2', 'app3', 'app4'] as $k)
                        <li><a href="{{ lroute('home') }}#applications">{{ __('frontend.home.ix.' . $k . '_t') }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4>{{ __('frontend.footer.company') }}</h4>
                <ul>
                    <li><a href="{{ lroute('about') }}">{{ __('frontend.nav.about') }}</a></li>
                    <li><a href="{{ lroute('news.index') }}">{{ __('frontend.nav.news') }}</a></li>
                    <li><a href="{{ lroute('contact') }}">{{ __('frontend.nav.contact') }}</a></li>
                </ul>
            </div>
            <div>
                <h4>{{ __('frontend.footer.support') }}</h4>
                <ul>
                    <li><a href="{{ lroute('knowledge.index') }}">{{ __('frontend.nav.knowledge') }}</a></li>
                    <li><a href="{{ lroute('contact') }}">{{ __('frontend.nav.quote') }}</a></li>
                </ul>
            </div>
        </div>

        <div class="ix-ft-bot">
            <span>&copy; {{ date('Y') }} {{ $site?->name ?? config('app.name') }}. {{ __('frontend.footer.all_rights_reserved') }}.</span>
            <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap">
                <x-lang-switcher />
                @if(!empty($socialLinks))
                <div class="ix-soc">
                    @foreach($socialLinks as $key => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $socialLabels[$key] ?? ucfirst($key) }}" class="ix-mono" style="font-size:11px;letter-spacing:.06em">{{ strtoupper(substr($socialLabels[$key] ?? $key, 0, 2)) }}</a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</footer>
