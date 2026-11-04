@extends('layouts.app')

@section('content')
{{-- Breadcrumb --}}
<nav class="bg-lsteel border-b border-line py-3">
    <div class="ix-wrap">
        <ol class="flex flex-wrap items-center gap-x-2 font-mono text-xs uppercase tracking-wider">
            <li><a href="{{ lroute('home') }}" class="text-steel hover:text-eblue transition">{{ __('frontend.common.home') }}</a></li>
            <li class="text-line">/</li>
            <li class="text-ink font-medium">{{ __('frontend.video.title') }}</li>
        </ol>
    </div>
</nav>

<x-page-hero
    :title="__('frontend.video.title')"
    :subtitle="__('frontend.video.subtitle')"
    image="https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=1920&q=80" />

<section class="py-12 md:py-20 bg-white">
    <div class="ix-wrap">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($videos as $video)
            <article class="ind-card">
                <a href="{{ $video->url }}" class="group block relative overflow-hidden border-b border-line aspect-video bg-navy" aria-label="{{ __('frontend.video.watch') }}: {{ $video->title }}">
                    <img src="{{ $video->thumbnail_url }}"
                         alt="{{ $video->title }}"
                         class="w-full h-full object-cover"
                         loading="lazy"
                         width="480"
                         height="360">
                    <span class="absolute inset-0 flex items-center justify-center bg-navy/20 group-hover:bg-navy/40 transition">
                        <span class="flex items-center justify-center w-14 h-14 bg-eblue text-white" style="border-radius: 2px;">
                            <svg class="w-6 h-6 ml-0.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                        </span>
                    </span>
                </a>
                <div class="p-6">
                    <h2 class="ix-n-t text-xl text-ink mb-3 line-clamp-2">
                        <a href="{{ $video->url }}" class="hover:text-eblue transition">{{ $video->title }}</a>
                    </h2>
                    @if($video->description)
                    <p class="text-steel line-clamp-3">{{ Str::limit(strip_tags($video->description), 100) }}</p>
                    @endif
                    <a href="{{ $video->url }}" class="ix-alink mt-3 text-eblue">
                        {{ __('frontend.video.watch') }} →
                    </a>
                </div>
            </article>
            @empty
            <div class="col-span-full text-center py-16 text-muted">
                <svg class="w-16 h-16 mx-auto mb-4 text-line" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.55-2.28A1 1 0 0121 8.62v6.76a1 1 0 01-1.45.9L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <p>{{ __('frontend.video.empty') }}</p>
            </div>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $videos->links() }}
        </div>
    </div>
</section>
@endsection

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => __('frontend.common.home'), 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => __('frontend.video.title')],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
