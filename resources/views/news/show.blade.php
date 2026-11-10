@extends('layouts.app')

@section('content')
{{-- Breadcrumb --}}
<nav class="bg-lsteel border-b border-line py-3">
    <div class="ix-wrap">
        <ol class="flex flex-wrap items-center gap-x-2 font-mono text-xs uppercase tracking-wider">
            <li><a href="{{ lroute('home') }}" class="text-steel hover:text-eblue transition">{{ __('frontend.common.home') }}</a></li>
            <li class="text-line">/</li>
            <li><a href="{{ lroute('news.index') }}" class="text-steel hover:text-eblue transition">{{ __('frontend.news.title') }}</a></li>
            <li class="text-line">/</li>
            <li class="text-ink font-medium line-clamp-1">{{ $news->title }}</li>
        </ol>
    </div>
</nav>

<article class="py-12 md:py-20 bg-white">
    <div class="max-w-4xl mx-auto px-5 sm:px-8">
        {{-- Article Header --}}
        <header class="mb-10">
            <div class="ix-n-meta mb-5">
                <time datetime="{{ $news->published_at?->toISOString() }}">
                    {{ $news->published_at?->format('M j, Y') }}
                </time>
                @if($news->category)
                <b>{{ $news->category->name }}</b>
                @endif
            </div>
            <h1 class="ix-d-md text-ink" style="text-transform: none;">
                {{ $news->title }}
            </h1>
        </header>

        {{-- Featured Image --}}
        @if($news->image_url)
        <figure class="mb-8">
            <img src="{{ $news->image_url }}"
                 alt="{{ $news->meta_keywords ?? $news->title }}"
                 class="w-full h-auto border border-line" style="border-radius: 2px;"
                 width="800"
                 height="450">
        </figure>
        @endif

        {{-- Article Content --}}
        <div class="prose prose-lg max-w-none">
            {!! $news->content !!}
        </div>


    </div>

    {{-- Related News --}}
    @if($relatedNews->count() > 0)
    <section class="ix-wrap mt-16 pt-16 border-t border-line">
        <h2 class="ix-d-md text-ink mb-8">{{ __('frontend.news.related') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($relatedNews as $related)
            <a href="{{ lroute('news.show', ['id' => $related->id, 'slug' => $related->slug ?? 'news']) }}"
               class="ind-card">
                @if($related->image_url)
                <img src="{{ $related->image_url }}"
                     alt="{{ $related->title }}"
                     class="w-full h-40 object-cover"
                     loading="lazy">
                @endif
                <div class="p-4">
                    <div class="ix-n-meta"><time>{{ $related->published_at?->format('Y/m/d') }}</time></div>
                    <h3 class="ix-n-t text-ink mt-2 line-clamp-2">
                        {{ $related->title }}
                    </h3>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif
</article>
@endsection

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $news->title,
    'description' => $news->meta_description ?? Str::limit(strip_tags($news->content), 160),
    'image' => $news->image_url ?? '',
    'datePublished' => $news->published_at?->toISOString(),
    'dateModified' => $news->updated_at?->toISOString(),
    'author' => [
        '@type' => 'Organization',
        'name' => $site?->name ?? config('app.name'),
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => $site?->name ?? config('app.name'),
        'logo' => [
            '@type' => 'ImageObject',
            'url' => $site?->logo_url ?? asset('images/logo.png'),
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => __('frontend.common.home'), 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => __('frontend.news.title'), 'item' => lroute('news.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $news->title],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
@endpush
