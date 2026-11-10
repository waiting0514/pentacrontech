@extends('layouts.app')

@section('content')
{{-- Breadcrumb --}}
<nav class="bg-lsteel border-b border-line py-3">
    <div class="ix-wrap">
        <ol class="flex flex-wrap items-center gap-x-2 font-mono text-xs uppercase tracking-wider">
            <li><a href="{{ lroute('home') }}" class="text-steel hover:text-eblue transition">{{ __('frontend.common.home') }}</a></li>
            <li class="text-line">/</li>
            <li><a href="{{ lroute('products.index') }}" class="text-steel hover:text-eblue transition">{{ __('frontend.product.title') }}</a></li>
            <li class="text-line">/</li>
            <li class="text-ink font-medium">{{ $product->name }}</li>
        </ol>
    </div>
</nav>

<article class="py-12 md:py-20 bg-white">
    <div class="ix-wrap">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            {{-- Product Images --}}
            @php
                $primaryImg = $product->primaryImage ?? $product->images->first();
                $allImages = $product->images;
                $primaryIndex = $allImages->search(fn($img) => $img->id === ($primaryImg->id ?? null)) ?: 0;
            @endphp
            <div x-data="{
                current: {{ $primaryIndex }},
                images: {{ Js::from($allImages->map(fn($img) => ['main' => asset('storage/' . $img->paths['main']), 'tiny' => asset('storage/' . $img->paths['tiny'])])->values()) }},
                lightbox: false,
                get total() { return this.images.length },
                prev() { this.current = (this.current - 1 + this.total) % this.total },
                next() { this.current = (this.current + 1) % this.total },
            }">
                @if($primaryImg)
                {{-- Main Image with Navigation --}}
                <div class="relative bg-off overflow-hidden mb-4 border border-line group" style="border-radius: 2px;">
                    <img :src="images[current].main"
                         alt="{{ $product->meta_keywords ?? $product->name }}"
                         class="w-full h-auto object-cover cursor-zoom-in"
                         width="1000" height="1000"
                         x-on:click="lightbox = true">
                    @if($allImages->count() > 1)
                    <button x-on:click="prev()" class="absolute left-2 top-1/2 -translate-y-1/2 w-12 h-12 bg-navy/60 hover:bg-eblue text-white rounded-sm flex items-center justify-center opacity-0 group-hover:opacity-100 focus-visible:opacity-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button x-on:click="next()" class="absolute right-2 top-1/2 -translate-y-1/2 w-12 h-12 bg-navy/60 hover:bg-eblue text-white rounded-sm flex items-center justify-center opacity-0 group-hover:opacity-100 focus-visible:opacity-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    @endif
                </div>
                @endif

                {{-- Thumbnails --}}
                @if($allImages->count() > 1)
                <div class="grid grid-cols-5 gap-2">
                    @foreach($allImages as $idx => $img)
                    <button x-on:click="current = {{ $idx }}"
                            class="rounded-sm overflow-hidden border-2 transition"
                            :class="current === {{ $idx }} ? 'border-eblue' : 'border-line hover:border-eblue'">
                        <img src="{{ asset('storage/' . $img->paths['tiny']) }}"
                             alt="" class="w-full aspect-square object-cover" loading="lazy">
                    </button>
                    @endforeach
                </div>
                @endif

                {{-- Lightbox --}}
                <div x-show="lightbox" x-cloak x-transition.opacity
                     class="fixed inset-0 z-50 flex items-center justify-center bg-navy/90"
                     x-on:click.self="lightbox = false" x-on:keydown.escape.window="lightbox = false">
                    <button x-on:click="lightbox = false" class="absolute top-4 right-4 w-12 h-12 bg-white/15 hover:bg-white/30 text-white rounded-sm flex items-center justify-center z-10">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    @if($allImages->count() > 1)
                    <button x-on:click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/15 hover:bg-white/30 text-white rounded-sm flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button x-on:click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/15 hover:bg-white/30 text-white rounded-sm flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    @endif
                    <img :src="images[current].main" class="max-w-[90vw] max-h-[85vh] object-contain" style="border-radius: 2px;" alt="">
                    <div class="absolute bottom-4 text-white/70 font-mono text-xs tracking-widest" x-text="(current + 1) + ' / ' + total"></div>
                </div>
            </div>

            {{-- Product Info --}}
            <div>
                <p class="ix-eyebrow mb-6"><b>{{ __('frontend.product.title') }}</b></p>
                <h1 class="ix-d-md text-ink mb-6">
                    {{ $product->name }}
                </h1>

                @if(false)
                @if($product->is_inquiry_only)
                    <div class="text-3xl font-extrabold text-steel mb-6">
                        {{ __('frontend.product.inquiry_welcome') }}
                    </div>
                @else
                    <div class="text-3xl font-extrabold text-eblue mb-6">
                        NT$ {{ number_format($product->price) }}
                    </div>
                @endif
                @endif

                {{-- Description --}}
                <div class="prose prose-lg max-w-none mb-8">
                    {!! nl2br($product->description) !!}
                </div>

                {{-- Specs --}}
                @if($product->specs)
                <div class="border-t border-line pt-6">
                    <h2 class="font-mono text-xs font-medium text-ink mb-4 uppercase tracking-widest">{{ __('frontend.product.specifications') }}</h2>
                    <div class="bg-off p-4 border border-line" style="border-radius: 2px;">
                        <pre class="text-sm text-steel whitespace-pre-wrap font-mono">{{ $product->specs }}</pre>
                    </div>
                </div>
                @endif

                {{-- Downloads --}}
                @php
                    $downloadItems = $product->attachments
                        ->map(fn($a) => ['attachment' => $a, 'file' => $a->fileFor()])
                        ->filter(fn($i) => $i['file']);
                @endphp
                @if($downloadItems->isNotEmpty())
                <section class="border-t border-line pt-6 mt-8" id="downloads">
                    <h2 class="font-mono text-xs font-medium text-ink mb-4 uppercase tracking-widest">{{ __('frontend.product.downloads') }}</h2>
                    <ul class="border border-line bg-white divide-y divide-line" style="border-radius: 2px;">
                        @foreach($downloadItems as $item)
                        @php
                            $file = $item['file'];
                            $dlTitle = $item['attachment']->displayTitle() ?: $file->original_name;
                        @endphp
                        <li>
                            <a href="{{ $file->url }}" download="{{ $file->original_name }}" target="_blank" rel="noopener"
                               class="group flex items-center gap-4 p-4 hover:bg-off transition"
                               aria-label="{{ __('frontend.product.download_pdf') }}: {{ $dlTitle }}">
                                <svg class="w-8 h-8 shrink-0 text-eblue" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 3H7a2 2 0 00-2 2v14a2 2 0 002 2h10a2 2 0 002-2V8l-5-5zM14 3v5h5"/>
                                    <text x="12" y="17" text-anchor="middle" font-size="5" font-weight="700" fill="currentColor" stroke="none" font-family="monospace">PDF</text>
                                </svg>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-ink font-medium break-words group-hover:text-eblue transition">{{ $dlTitle }}</span>
                                    <span class="mt-1 flex flex-wrap items-center gap-2 font-mono text-xs text-steel">
                                        <span>PDF · {{ $file->human_size }}</span>
                                        @if($file->locale !== app()->getLocale())
                                        <span class="border border-line px-1.5 py-0.5 uppercase tracking-wider text-steel" style="border-radius: 2px;">{{ $file->locale }}</span>
                                        @endif
                                    </span>
                                </span>
                                <svg class="w-5 h-5 shrink-0 text-steel group-hover:text-eblue transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                                </svg>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </section>
                @endif

                {{-- Add to Inquiry Cart + Toast --}}
                @if(false)
                <div class="mt-8" x-data="{ added: false, qty: 1 }">
                    {{-- Quantity Selector --}}
                    <div class="flex items-center gap-3 mb-8">
                        <span class="text-sm font-semibold text-steel">{{ __('frontend.inquiry.quantity') }}</span>
                        <div class="inline-flex items-center border border-line rounded-sm">
                            <button @click="qty = Math.max(1, qty - 1)"
                                    :disabled="qty <= 1"
                                    class="px-4 py-2 text-steel hover:text-ink disabled:opacity-30 disabled:cursor-not-allowed transition-colors select-none text-lg leading-none">−</button>
                            <input type="number" x-model.number="qty"
                                   min="1" max="9999"
                                   @change="qty = Math.min(9999, Math.max(1, parseInt(qty) || 1))"
                                   @focus="$event.target.select()"
                                   class="w-14 py-2 text-center text-base font-bold text-ink border-x border-line focus:outline-none focus:border-eblue [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                            <button @click="qty = Math.min(9999, qty + 1)"
                                    :disabled="qty >= 9999"
                                    class="px-4 py-2 text-steel hover:text-ink disabled:opacity-30 disabled:cursor-not-allowed transition-colors select-none text-lg leading-none">+</button>
                        </div>
                    </div>

                    <button
                        @click="
                            $store.cart.add({
                                product_id: {{ $product->id }},
                                name: {{ Js::from($product->name) }},
                                price: {{ $product->price ?? 0 }},
                                image: {{ Js::from($product->primaryImage ? asset('storage/' . $product->primaryImage->paths['thumb']) : '') }}
                            }, qty);
                            added = true;
                            setTimeout(() => added = false, 2000);
                        "
                        class="inline-flex items-center px-8 py-4 ind-btn rounded text-lg"
                    >
                        <svg class="mr-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span x-show="!added">{{ __('frontend.inquiry.add_to_cart') }}</span>
                        <span x-show="added" x-cloak>✓ {{ __('frontend.inquiry.added') }}</span>
                    </button>

                    {{-- Toast --}}
                    <div x-show="added" x-cloak x-transition
                         class="fixed bottom-6 right-6 z-50 bg-navy text-white px-5 py-3 rounded-sm flex items-center gap-2 text-sm font-semibold">
                        <svg class="w-5 h-5 text-plight" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ __('frontend.inquiry.add_to_cart_success') }}
                    </div>
                </div>
                @endif

                    {{-- Original contact link --}}
                    <a href="{{ lroute('contact') }}"
                       class="ix-btn ix-btn-p mt-4">
                        {{ __('frontend.product.contact_now') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </a>
                </div>
            </div>

        {{-- Related Products --}}
        @if($relatedProducts->count() > 0)
        <section class="mt-16 pt-16 border-t border-line">
            <h2 class="ix-d-md text-ink" style="margin-bottom:32px">{{ __('frontend.product.related') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($relatedProducts as $related)
                <a href="{{ lroute('products.show', ['id' => $related->id, 'slug' => $related->slug ?: 'product']) }}"
                   class="ind-card group">
                    @php $relPrimary = $related->primaryImage ?? $related->images->first(); @endphp
                    @if($relPrimary)
                    <div class="aspect-square bg-off flex items-center justify-center p-4">
                        <img src="{{ asset('storage/' . $relPrimary->paths['thumb']) }}"
                             alt="{{ $related->name }}"
                             class="max-w-full max-h-full object-contain group-hover:scale-105 transition duration-300"
                             loading="lazy">
                    </div>
                    @else
                    <div class="aspect-square bg-off flex items-center justify-center p-4">
                        <svg class="w-12 h-12 text-line" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    @endif
                    <div class="p-4 border-t border-line">
                        <h3 class="ix-h-zh text-base text-ink group-hover:text-eblue transition">{{ $related->name }}</h3>
                        @if(false)
                        @if($related->is_inquiry_only)
                            <p class="text-steel font-bold mt-1">{{ __('frontend.product.inquiry_welcome') }}</p>
                        @else
                            <p class="text-eblue font-extrabold mt-1">NT$ {{ number_format($related->price) }}</p>
                        @endif
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</article>
@endsection

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product->name,
    'description' => $product->meta_description ?: Str::limit($product->description, 160),
    'image' => ($product->primaryImage ?? $product->images->first()) ? asset('storage/' . ($product->primaryImage ?? $product->images->first())->paths['main']) : '',
    'sku' => (string) $product->id,
    'brand' => [
        '@type' => 'Brand',
        'name' => $site?->name ?? config('app.name'),
    ],
    'offers' => [
        '@type' => 'Offer',
        'url' => url()->current(),
        'priceCurrency' => 'TWD',
        'price' => $product->price,
        'availability' => 'https://schema.org/InStock',
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => __('frontend.common.home'), 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => __('frontend.product.title'), 'item' => lroute('products.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $product->name],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
@endpush
