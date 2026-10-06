@extends('layouts.app')

@section('content')
{{-- Breadcrumb --}}
<nav class="bg-gray-100 border-b border-gray-200 py-3">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ lroute('home') }}" class="text-gray-500 hover:text-[#BE185D] transition">{{ __('frontend.common.home') }}</a></li>
            <li class="text-gray-300">/</li>
            <li class="text-[#1a1a1a] font-bold">{{ __('frontend.knowledge.title') }}</li>
        </ol>
    </div>
</nav>

<x-page-hero
    :title="__('frontend.knowledge.title')"
    :subtitle="__('frontend.knowledge.subtitle')"
    image="https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=1920&q=80" />

<section class="py-12 md:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($articleList as $article)
            <article class="ind-card rounded-lg overflow-hidden flex flex-col">
                @if($article->image_url)
                <div class="overflow-hidden">
                    <img src="{{ $article->image_url }}"
                         alt="{{ $article->meta_keywords ?? $article->title }}"
                         class="w-full h-48 object-cover"
                         loading="lazy"
                         width="400"
                         height="300">
                </div>
                @endif
                <div class="p-6 flex flex-col flex-1">
                    <div class="flex items-center gap-2 mb-3 flex-wrap">
                        @if($article->category)
                        <a href="{{ lroute('knowledge.category', ['categorySlug' => $article->category->slug]) }}"
                           class="px-2.5 py-1 text-xs font-semibold bg-pink-50 text-[#BE185D] rounded-full hover:bg-pink-100 transition">
                            {{ $article->category->name }}
                        </a>
                        @endif
                        @if($article->reading_time)
                        <span class="text-xs text-gray-400">{{ $article->reading_time }} min read</span>
                        @endif
                        <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded">
                            {{ match($article->schema_type) {
                                'faq' => 'FAQ',
                                'howto' => 'How-To',
                                'tutorial' => 'Tutorial',
                                default => 'Article',
                            } }}
                        </span>
                    </div>
                    <h2 class="text-xl font-bold text-[#1a1a1a] mb-3 line-clamp-2">
                        {{ $article->title }}
                    </h2>
                    @if($article->excerpt)
                    <p class="text-gray-500 line-clamp-3 flex-1">{{ $article->excerpt }}</p>
                    @endif
                    <a href="{{ lroute('knowledge.show', ['categorySlug' => ($article->category?->slug ?: 'general'), 'id' => $article->id, 'slug' => ($article->slug ?: 'article')]) }}"
                       class="inline-block mt-4 text-[#1a1a1a] hover:text-[#BE185D] font-bold transition uppercase text-sm">
                        {{ __('frontend.knowledge.read_more') }}
                    </a>
                </div>
            </article>
            @empty
            <div class="col-span-full text-center py-16 text-gray-400">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <p>{{ __('frontend.knowledge.no_articles') }}</p>
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
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => __('frontend.common.home'), 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => __('frontend.knowledge.title')],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
