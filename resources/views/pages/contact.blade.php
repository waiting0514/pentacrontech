@extends('layouts.app')

@section('content')

{{-- Breadcrumb --}}
<nav class="bg-gray-100 border-b border-gray-200 py-3">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ lroute('home') }}" class="text-gray-500 hover:text-[#BE185D] transition">{{ __('frontend.common.home') }}</a></li>
            <li class="text-gray-300">/</li>
            <li class="text-[#1a1a1a] font-bold">{{ __('frontend.contact.title') }}</li>
        </ol>
    </div>
</nav>

{{-- Hero --}}
<x-page-hero
    :title="__('frontend.contact.title')"
    :subtitle="__('frontend.contact.subtitle')"
    image="https://images.unsplash.com/photo-1423666639041-f56000c27a9a?w=1920&q=80" />

<section class="py-12 md:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            {{-- Contact Form --}}
            <div class="bg-white rounded-xl border border-gray-200 p-8 shadow-sm">
                <h2 class="text-2xl font-bold text-[#1a1a1a] mb-6">{{ __('frontend.contact.form_title') }}</h2>

                @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-lg border border-green-200">
                    {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ lroute('contact.submit') }}" method="POST" x-data="{ submitting: false }" @submit="submitting = true">
                    @csrf

                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-bold text-[#1a1a1a] mb-2 uppercase tracking-wide">{{ __('frontend.contact.name') }} *</label>
                            <input type="text" id="name" name="name" required
                                   value="{{ old('name') }}"
                                   class="w-full px-4 py-3 rounded-lg ind-input">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-bold text-[#1a1a1a] mb-2 uppercase tracking-wide">{{ __('frontend.contact.email') }} *</label>
                            <input type="email" id="email" name="email" required
                                   value="{{ old('email') }}"
                                   class="w-full px-4 py-3 rounded-lg ind-input">
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-bold text-[#1a1a1a] mb-2 uppercase tracking-wide">{{ __('frontend.contact.phone') }}</label>
                            <input type="tel" id="phone" name="phone"
                                   value="{{ old('phone') }}"
                                   class="w-full px-4 py-3 rounded-lg ind-input">
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-bold text-[#1a1a1a] mb-2 uppercase tracking-wide">{{ __('frontend.contact.message') }} *</label>
                            <textarea id="message" name="message" rows="5" required
                                      class="w-full px-4 py-3 rounded-lg ind-input resize-none">{{ old('message') }}</textarea>
                        </div>

                        {{-- CAPTCHA --}}
                        <x-captcha :provider="$captchaProvider" :site-key="$captchaSiteKey" />

                        <button type="submit"
                                :disabled="submitting"
                                class="w-full px-6 py-4 ind-btn rounded-lg text-lg disabled:opacity-50">
                            <span x-show="!submitting">{{ __('frontend.contact.send') }}</span>
                            <span x-show="submitting" x-cloak>{{ __('frontend.contact.sending') }}</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Contact Info & Map --}}
            <div class="space-y-8">
                {{-- Contact Info --}}
                <div class="bg-gray-900 rounded-xl p-8">
                    <h2 class="text-2xl font-bold text-white mb-6">{{ __('frontend.contact.info_title') }}</h2>

                    <div class="space-y-4">
                        @if($site?->contact_email)
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-[#BE185D] mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <div>
                                <p class="font-bold text-white">{{ __('frontend.contact.email') }}</p>
                                <a href="mailto:{{ $site->contact_email }}" class="text-[#BE185D] hover:underline">
                                    {{ $site->contact_email }}
                                </a>
                            </div>
                        </div>
                        @endif

                        @if($site?->contact_phone)
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-[#BE185D] mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            <div>
                                <p class="font-bold text-white">{{ __('frontend.contact.phone') }}</p>
                                <a href="tel:{{ $site->contact_phone }}" class="text-[#BE185D] hover:underline">
                                    {{ $site->contact_phone }}
                                </a>
                            </div>
                        </div>
                        @endif

                        @if($site?->contact_address)
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-[#BE185D] mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <div>
                                <p class="font-bold text-white">{{ __('frontend.contact.address') }}</p>
                                <p class="text-gray-400">{{ $site->contact_address }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Google Map --}}
                @if($site?->geo_lat && $site?->geo_lng)
                <div class="rounded-lg overflow-hidden border border-gray-200">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3000!2d{{ $site->geo_lng }}!3d{{ $site->geo_lat }}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1"
                        width="100%"
                        height="300"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

<x-captcha-script :provider="$captchaProvider" :site-key="$captchaSiteKey" />

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'LocalBusiness',
    'name' => $site?->name ?? config('app.name'),
    'image' => $site?->logo_url ?? '',
    'email' => $site?->contact_email ?? '',
    'telephone' => $site?->contact_phone ?? '',
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $site?->contact_address ?? '',
    ],
    'geo' => ($site?->geo_lat && $site?->geo_lng) ? [
        '@type' => 'GeoCoordinates',
        'latitude' => $site->geo_lat,
        'longitude' => $site->geo_lng,
    ] : null,
    'url' => url('/'),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => __('frontend.common.home'), 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => __('frontend.contact.title')],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
