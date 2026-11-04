@extends('layouts.app')

@section('content')
{{-- Breadcrumb --}}
<nav class="bg-lsteel border-b border-line py-3">
    <div class="ix-wrap">
        <ol class="flex flex-wrap items-center gap-x-2 font-mono text-xs uppercase tracking-wider">
            <li><a href="{{ lroute('home') }}" class="text-steel hover:text-eblue transition">{{ __('frontend.common.home') }}</a></li>
            <li class="text-line">/</li>
            <li><a href="{{ lroute('knowledge.index') }}" class="text-steel hover:text-eblue transition">{{ __('frontend.knowledge.title') }}</a></li>
            <li class="text-line">/</li>
            <li class="text-ink font-medium">{{ $category->name }}</li>
        </ol>
    </div>
</nav>

<x-page-hero
    :title="$category->name"
    :subtitle="$category->description ?: __('frontend.knowledge.subtitle')"
    image="https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=1920&q=80" />

<section class="py-12 md:py-20 bg-white">
    <div class="ix-wrap">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($articleList as $article)
            <article class="ind-card flex flex-col">
                @if($article->image_url)
                <div class="overflow-hidden border-b border-line">
                    <img src="{{ $article->image_url }}"
                         alt="{{ $article->meta_keywords ?? $article->title }}"
                         class="w-full h-48 object-cover"
                         loading="lazy"
                         width="400"
                         height="300">
                </div>
                @endif
                <div class="p-6 flex flex-col flex-1">
                    <div class="flex items-center gap-2 mb-3 flex-wrap font-mono text-xs uppercase tracking-wider">
                        @if($article->reading_time)
                        <span class="text-muted">{{ $article->reading_time }} min read</span>
                        @endif
                        <span class="px-2 py-1 border border-line text-muted rounded-sm">
                            {{ match($article->schema_type) {
                                'faq' => 'FAQ',
                                'howto' => 'How-To',
                                'tutorial' => 'Tutorial',
                                default => 'Article',
                            } }}
                        </span>
                    </div>
                    <h2 class="ix-n-t text-xl text-ink mb-3 line-clamp-2">{{ $article->title }}</h2>
                    @if($article->excerpt)
                    <p class="text-steel line-clamp-3 flex-1">{{ $article->excerpt }}</p>
                    @endif
                    <a href="{{ lroute('knowledge.show', ['categorySlug' => ($article->category?->slug ?: 'general'), 'id' => $article->id, 'slug' => ($article->slug ?: 'article')]) }}"
                       class="ix-alink mt-3 text-eblue">
                        {{ __('frontend.knowledge.read_more') }}
                    </a>
                </div>
            </article>
            @empty
            <div class="col-span-full text-center py-16 text-muted">
                <p>{{ __('frontend.knowledge.no_articles_in_category') }}</p>
            </div>
            @endforelse
        </div>

        <div class="mt-12">
            {{ $articleList->links() }}
        </div>
    </div>
</section>
@endsection

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $category->name,
    'description' => $category->description ?: ($category->name . ' ' . __('frontend.knowledge.related')),
    'url' => lroute('knowledge.category', ['categorySlug' => $category->slug]),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => __('frontend.common.home'), 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => __('frontend.knowledge.title'), 'item' => lroute('knowledge.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $category->name],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
