@extends('layouts.app')

@section('content')
{{-- Breadcrumb --}}
<nav class="bg-lsteel border-b border-line py-3">
    <div class="ix-wrap">
        <ol class="flex flex-wrap items-center gap-x-2 font-mono text-xs uppercase tracking-wider">
            <li><a href="{{ lroute('home') }}" class="text-steel hover:text-eblue transition">{{ __('frontend.common.home') }}</a></li>
            <li class="text-line">/</li>
            <li><a href="{{ lroute('videos.index') }}" class="text-steel hover:text-eblue transition">{{ __('frontend.video.title') }}</a></li>
            <li class="text-line">/</li>
            <li class="text-ink font-medium line-clamp-1">{{ $video->title }}</li>
        </ol>
    </div>
</nav>

<article class="py-12 md:py-20 bg-white">
    <div class="max-w-4xl mx-auto px-5 sm:px-8">
        <header class="mb-8">
            <h1 class="ix-d-md text-ink" style="text-transform: none;">{{ $video->title }}</h1>
        </header>

        {{-- Player (16:9, never autoplay) --}}
        <div class="relative w-full aspect-video overflow-hidden border border-line bg-navy" style="border-radius: 2px;">
            <iframe src="{{ $video->embed_url }}"
                    loading="lazy"
                    allow="accelerometer; encrypted-media; picture-in-picture; fullscreen"
                    allowfullscreen
                    referrerpolicy="strict-origin-when-cross-origin"
                    title="{{ $video->title }}"
                    class="absolute inset-0 w-full h-full border-0"></iframe>
        </div>

        @if($video->description)
        <div class="mt-8 text-steel text-lg leading-relaxed break-words">
            {!! nl2br(e($video->description)) !!}
        </div>
        @endif

        <div class="mt-10">
            <a href="{{ lroute('videos.index') }}" class="ix-alink text-eblue">← {{ __('frontend.video.back_to_list') }}</a>
        </div>
    </div>

    {{-- Related Videos --}}
    @if($relatedVideos->count() > 0)
    <div class="ix-wrap">
    <section class="mt-16 pt-16 border-t border-line">
        <h2 class="ix-d-md text-ink" style="margin-bottom:32px">{{ __('frontend.video.related') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($relatedVideos as $related)
            <a href="{{ $related->url }}" class="ind-card group block">
                <div class="relative aspect-video bg-navy overflow-hidden">
                    <img src="{{ $related->thumbnail_url }}"
                         alt="{{ $related->title }}"
                         class="w-full h-full object-cover"
                         loading="lazy">
                    <span class="absolute inset-0 flex items-center justify-center bg-navy/20 group-hover:bg-navy/40 transition">
                        <span class="flex items-center justify-center w-12 h-12 bg-eblue text-white" style="border-radius: 2px;">
                            <svg class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                        </span>
                    </span>
                </div>
                <div class="p-4">
                    <h3 class="ix-n-t text-ink line-clamp-2">{{ $related->title }}</h3>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    </div>
    @endif
</article>
@endsection

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'VideoObject',
    'name' => $video->title,
    'description' => $metaDescription ?: $video->title,
    'thumbnailUrl' => $video->thumbnail_url,
    'embedUrl' => $video->embed_url,
    'contentUrl' => $video->youtube_url,
    'uploadDate' => $video->created_at?->toIso8601String(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => __('frontend.common.home'), 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => __('frontend.video.title'), 'item' => lroute('videos.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $video->title],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
