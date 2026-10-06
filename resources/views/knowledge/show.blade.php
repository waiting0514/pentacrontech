@extends('layouts.app')

@section('content')
{{-- Breadcrumb --}}
<nav class="bg-gray-100 border-b border-gray-200 py-3">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ lroute('home') }}" class="text-gray-500 hover:text-[#BE185D] transition">{{ __('frontend.common.home') }}</a></li>
            <li class="text-gray-300">/</li>
            <li><a href="{{ lroute('knowledge.index') }}" class="text-gray-500 hover:text-[#BE185D] transition">{{ __('frontend.knowledge.title') }}</a></li>
            @if($article->category)
            <li class="text-gray-300">/</li>
            <li><a href="{{ lroute('knowledge.category', ['categorySlug' => $article->category->slug]) }}"
                   class="text-gray-500 hover:text-[#BE185D] transition">{{ $article->category->name }}</a></li>
            @endif
            <li class="text-gray-300">/</li>
            <li class="text-[#1a1a1a] font-bold line-clamp-1">{{ $article->title }}</li>
        </ol>
    </div>
</nav>

<article class="py-12 md:py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Article Header --}}
        <header class="mb-8">
            <div class="flex items-center gap-3 flex-wrap mb-4">
                @if($article->category)
                <a href="{{ lroute('knowledge.category', ['categorySlug' => $article->category->slug]) }}"
                   class="px-3 py-1 bg-pink-50 text-[#BE185D] rounded-full font-semibold text-xs hover:bg-pink-100 transition">
                    {{ $article->category->name }}
                </a>
                @endif
                <span class="text-xs px-2.5 py-1 bg-gray-100 text-gray-500 rounded-full font-medium">
                    {{ match($article->schema_type) {
                        'faq' => 'FAQ',
                        'howto' => 'How-To',
                        'tutorial' => 'Tutorial',
                        default => 'Article',
                    } }}
                </span>
                @if($article->reading_time)
                <span class="text-sm text-gray-400">{{ $article->reading_time }} min read</span>
                @endif
            </div>
            <div class="w-12 h-1 bg-[#BE185D] mb-4"></div>
            <h1 class="text-3xl md:text-4xl font-black text-[#1a1a1a] tracking-tight">
                {{ $article->title }}
            </h1>
            @if($article->excerpt)
            <p class="mt-4 text-xl text-gray-600 leading-relaxed">{{ $article->excerpt }}</p>
            @endif
        </header>

        {{-- Featured Image --}}
        @if($article->image_url)
        <figure class="mb-8">
            <img src="{{ $article->image_url }}"
                 alt="{{ $article->meta_keywords ?? $article->title }}"
                 class="w-full h-auto rounded-lg border border-gray-200"
                 width="800"
                 height="450">
        </figure>
        @endif

        {{-- Article Content --}}
        <div class="prose prose-lg max-w-none">
            {!! $article->content !!}
        </div>
    </div>

    {{-- Related Articles --}}
    @if($relatedArticles->count() > 0)
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 pt-16 border-t border-gray-200">
        <h2 class="text-2xl font-black text-[#1a1a1a] mb-8">{{ __('frontend.knowledge.related') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($relatedArticles as $related)
            <a href="{{ lroute('knowledge.show', ['categorySlug' => ($related->category?->slug ?: 'general'), 'id' => $related->id, 'slug' => ($related->slug ?: 'article')]) }}"
               class="ind-card rounded-lg overflow-hidden">
                @if($related->image_url)
                <img src="{{ $related->image_url }}"
                     alt="{{ $related->title }}"
                     class="w-full h-40 object-cover"
                     loading="lazy">
                @endif
                <div class="p-4">
                    @if($related->reading_time)
                    <span class="text-xs text-gray-400">{{ $related->reading_time }} min read</span>
                    @endif
                    <h3 class="font-bold text-[#1a1a1a] mt-1 line-clamp-2">{{ $related->title }}</h3>
                    @if($related->excerpt)
                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $related->excerpt }}</p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif
</article>
@endsection

@push('schema')
@switch($article->schema_type)
    @case('faq')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect(explode('<h3>', strip_tags($article->content, '<h3><p>')))
            ->filter()
            ->map(function ($block) {
                $parts = explode('</h3>', $block, 2);
                if (count($parts) < 2) return null;
                return [
                    '@type' => 'Question',
                    'name' => strip_tags($parts[0]),
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($parts[1])],
                ];
            })
            ->filter()
            ->values()
            ->toArray(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @break

    @case('howto')
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'HowTo',
        'name' => $article->title,
        'description' => $article->excerpt ?? $article->meta_description ?? '',
        'step' => collect(explode('<li>', strip_tags($article->content, '<li>')))
            ->filter()
            ->values()
            ->map(fn($step, $i) => [
                '@type' => 'HowToStep',
                'position' => $i + 1,
                'text' => strip_tags($step),
            ])
            ->toArray(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
    @break

    @default
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'TechArticle',
        'headline' => $article->title,
        'description' => $article->excerpt ?? $article->meta_description ?? Str::limit(strip_tags($article->content), 160),
        'image' => $article->image_url ?? '',
        'dateModified' => $article->updated_at?->toISOString(),
        'wordCount' => str_word_count(strip_tags($article->content ?? '')),
        'author' => ['@type' => 'Organization', 'name' => $site?->name ?? config('app.name')],
        'publisher' => [
            '@type' => 'Organization',
            'name' => $site?->name ?? config('app.name'),
            'logo' => ['@type' => 'ImageObject', 'url' => $site?->logo_url ?? asset('images/logo.png')],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endswitch

<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => array_filter([
        ['@type' => 'ListItem', 'position' => 1, 'name' => __('frontend.common.home'), 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => __('frontend.knowledge.title'), 'item' => lroute('knowledge.index')],
        $article->category ? ['@type' => 'ListItem', 'position' => 3, 'name' => $article->category->name, 'item' => lroute('knowledge.category', ['categorySlug' => $article->category->slug])] : null,
        ['@type' => 'ListItem', 'position' => $article->category ? 4 : 3, 'name' => $article->title],
    ]),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
