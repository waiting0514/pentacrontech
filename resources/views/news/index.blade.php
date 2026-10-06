@extends('layouts.app')

@section('content')
{{-- Breadcrumb --}}
<nav class="bg-gray-100 border-b border-gray-200 py-3">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ lroute('home') }}" class="text-gray-500 hover:text-[#BE185D] transition">{{ __('frontend.common.home') }}</a></li>
            <li class="text-gray-300">/</li>
            <li class="text-[#1a1a1a] font-bold">{{ __('frontend.news.title') }}</li>
        </ol>
    </div>
</nav>

<x-page-hero
    :title="__('frontend.news.title')"
    :subtitle="__('frontend.news.stay_updated')"
    image="https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=1920&q=80" />

<section class="py-12 md:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- News Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($newsList as $news)
            <article class="ind-card rounded-lg overflow-hidden">
                @if($news->image_url)
                <div class="overflow-hidden">
                    <img src="{{ $news->image_url }}"
                         alt="{{ $news->meta_keywords ?? $news->title }}"
                         class="w-full h-48 object-cover"
                         loading="lazy"
                         width="400"
                         height="300">
                </div>
                @endif
                <div class="p-6">
                    <time class="text-sm text-[#BE185D] font-semibold" datetime="{{ $news->published_at?->toISOString() }}">
                        {{ $news->published_at?->format('Y/m/d') }}
                    </time>
                    @if($news->category)
                    <span class="ml-2 px-2.5 py-1 text-xs font-semibold bg-pink-50 text-[#BE185D] rounded-full">
                        {{ $news->category->name }}
                    </span>
                    @endif
                    <h2 class="text-xl font-bold text-[#1a1a1a] mt-2 mb-3 line-clamp-2">
                        {{ $news->title }}
                    </h2>
                    <p class="text-gray-500 line-clamp-3">
                        {{ Str::limit(strip_tags($news->content), 100) }}
                    </p>
                    <a href="{{ lroute('news.show', ['id' => $news->id, 'slug' => $news->slug ?? 'news']) }}"
                       class="inline-block mt-4 text-[#1a1a1a] hover:text-[#BE185D] font-bold transition uppercase text-sm">
                        {{ __('frontend.news.read_more') }} →
                    </a>
                </div>
            </article>
            @empty
            <div class="col-span-full text-center py-16 text-gray-400">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <p>{{ __('frontend.news.no_news') }}</p>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-12">
            {{ $newsList->links() }}
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
        ['@type' => 'ListItem', 'position' => 2, 'name' => __('frontend.news.title')],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
