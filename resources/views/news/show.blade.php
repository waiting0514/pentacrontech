@extends('layouts.app')

@section('content')
{{-- Breadcrumb --}}
<nav class="bg-gray-100 border-b border-gray-200 py-3">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ lroute('home') }}" class="text-gray-500 hover:text-[#BE185D] transition">{{ __('frontend.common.home') }}</a></li>
            <li class="text-gray-300">/</li>
            <li><a href="{{ lroute('news.index') }}" class="text-gray-500 hover:text-[#BE185D] transition">{{ __('frontend.news.title') }}</a></li>
            <li class="text-gray-300">/</li>
            <li class="text-[#1a1a1a] font-bold line-clamp-1">{{ $news->title }}</li>
        </ol>
    </div>
</nav>

<article class="py-12 md:py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Article Header --}}
        <header class="mb-8">
            <div class="flex items-center space-x-4 text-sm mb-4">
                <time datetime="{{ $news->published_at?->toISOString() }}" class="text-[#BE185D] font-semibold">
                    {{ $news->published_at?->format('Y年m月d日') }}
                </time>
                @if($news->category)
                <span class="px-3 py-1 bg-pink-50 text-[#BE185D] rounded-full font-semibold text-xs">
                    {{ $news->category->name }}
                </span>
                @endif
            </div>
            <div class="w-12 h-1 bg-[#BE185D] mb-4"></div>
            <h1 class="text-3xl md:text-4xl font-black text-[#1a1a1a] tracking-tight">
                {{ $news->title }}
            </h1>
        </header>

        {{-- Featured Image --}}
        @if($news->image_url)
        <figure class="mb-8">
            <img src="{{ $news->image_url }}"
                 alt="{{ $news->meta_keywords ?? $news->title }}"
                 class="w-full h-auto rounded-lg border border-gray-200"
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
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 pt-16 border-t border-gray-200">
        <h2 class="text-2xl font-black text-[#1a1a1a] mb-8">{{ __('frontend.news.related') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($relatedNews as $related)
            <a href="{{ lroute('news.show', ['id' => $related->id, 'slug' => $related->slug ?? 'news']) }}"
               class="ind-card rounded-lg overflow-hidden">
                @if($related->image_url)
                <img src="{{ $related->image_url }}"
                     alt="{{ $related->title }}"
                     class="w-full h-40 object-cover"
                     loading="lazy">
                @endif
                <div class="p-4">
                    <time class="text-sm text-[#BE185D] font-semibold">{{ $related->published_at?->format('Y/m/d') }}</time>
                    <h3 class="font-bold text-[#1a1a1a] mt-1 line-clamp-2">
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
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
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
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
