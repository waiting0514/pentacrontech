@extends('layouts.app')

@section('content')
{{-- Breadcrumb --}}
<nav class="bg-gray-100 border-b border-gray-200 py-3">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ lroute('home') }}" class="text-gray-500 hover:text-[#BE185D] transition">{{ __('frontend.common.home') }}</a></li>
            <li class="text-gray-300">/</li>
            @if(isset($currentCategory) && $currentCategory)
            <li><a href="{{ lroute('products.index') }}" class="text-gray-500 hover:text-[#BE185D] transition">{{ __('frontend.product.title') }}</a></li>
            <li class="text-gray-300">/</li>
            <li class="text-[#1a1a1a] font-bold">{{ $currentCategory->name }}</li>
            @else
            <li class="text-[#1a1a1a] font-bold">{{ __('frontend.product.all_categories') }}</li>
            @endif
        </ol>
    </div>
</nav>

<x-page-hero
    :title="$currentCategory?->name ?: __('frontend.product.all_categories')"
    :subtitle="$currentCategory ? (__('frontend.product.explore_category_range') ?: '探索此分類精選商品') : (__('frontend.product.explore_full_range') ?: '探索完整的產品系列')"
    image="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=1920&q=80" />

<section class="py-12 md:py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if(!isset($currentCategory) || !$currentCategory)
            {{-- Category Grid (Show All Categories) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($categories as $cat)
                <a href="{{ lroute('category.show', ['slug' => $cat->slug ?: (collect($cat->getTranslations('slug'))->first() ?: 'category')]) }}" class="group">
                    <div class="ind-card rounded-lg overflow-hidden transition-all duration-300 transform group-hover:-translate-y-1 group-hover:shadow-lg">
                        <div class="overflow-hidden aspect-video bg-gray-100 relative">
                            @if($cat->image_path)
                            <img src="{{ asset('storage/' . $cat->image_path) }}"
                                 alt="{{ $cat->name }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                                 loading="lazy">
                            @else
                            {{-- Geometric Placeholder --}}
                            <div class="w-full h-full bg-gradient-to-br from-gray-50 to-gray-200 flex items-center justify-center">
                                <svg class="w-16 h-16 text-gray-400 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            @endif
                        </div>
                        <div class="p-6 text-center">
                            <h2 class="text-xl font-bold text-[#1a1a1a] group-hover:text-[#BE185D] transition">
                                {{ $cat->name }}
                            </h2>
                            <p class="text-gray-500 text-sm mt-2">
                                {{ __('frontend.product.explore_category_range') }} →
                            </p>
                        </div>
                    </div>
                </a>
                @empty
                <div class="col-span-full text-center py-16 text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p>{{ __('frontend.product.no_categories') }}</p>
                </div>
                @endforelse
            </div>
        @else
            <div class="flex flex-col lg:flex-row gap-8">
                {{-- Sidebar Category List --}}
                <aside class="w-full lg:w-1/4 flex-shrink-0">
                    <div class="bg-white rounded-lg p-5 border border-gray-100 shadow-sm sticky top-6">
                        <h2 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-3 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#BE185D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                            {{ __('frontend.product.title') ?: '產品分類' }}
                        </h2>
                        
                        {{-- Mobile Scroll Container with Right Fade Overlay --}}
                        <div class="relative overflow-hidden lg:overflow-visible">
                            {{-- Right side fade overlay (pointer-events-none allows clicks to pass through) --}}
                            <div class="absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-white to-transparent pointer-events-none lg:hidden z-10"></div>
                            
                            <div class="flex lg:flex-col gap-2 overflow-x-auto lg:overflow-x-visible pb-3 lg:pb-0 whitespace-nowrap lg:whitespace-normal scrollbar-none" style="scrollbar-width: none; -ms-overflow-style: none;">
                                <style>
                                    .scrollbar-none::-webkit-scrollbar { display: none; }
                                </style>
                            <a href="{{ lroute('products.index') }}"
                               class="px-4 py-2.5 rounded-lg text-sm font-bold flex items-center gap-2 transition duration-200 border lg:border-none {{ !isset($currentCategory) || !$currentCategory ? 'bg-[#BE185D] text-white lg:bg-[#fdf2f8] lg:text-[#BE185D]' : 'bg-white text-gray-600 hover:bg-gray-50 hover:text-[#BE185D] border-gray-200' }}">
                                <span class="truncate">← {{ __('frontend.product.all_categories') }}</span>
                            </a>
                            
                            @php
                                // 將扁平集合依 parent_id 分組，便於快速遞迴查找
                                $categoriesGrouped = $categories->groupBy('parent_id');

                                // 輔助閉包：判斷一個分類 ID 是否是另一個分類 ID 的子孫節點（用於高亮展開狀態）
                                $isDescendant = function($descendantId, $ancestorId) use (&$isDescendant, $categoriesGrouped) {
                                    $directChildren = $categoriesGrouped->get($ancestorId);
                                    if (!$directChildren) return false;
                                    if ($directChildren->contains('id', $descendantId)) return true;
                                    foreach ($directChildren as $child) {
                                        if ($isDescendant($descendantId, $child->id)) return true;
                                    }
                                    return false;
                                };

                                // 遞迴渲染分類樹的閉包
                                $renderCategoryTree = function($parentId) use (&$renderCategoryTree, $categoriesGrouped, $currentCategory, $isDescendant) {
                                    $cats = $categoriesGrouped->get($parentId);
                                    if (!$cats) return;

                                    $isRoot = is_null($parentId);
                                    
                                    // 容器樣式：如果是子分類，綁定 x-show="open" 與 x-transition 以實現流暢的摺疊收合動畫
                                    $containerAttr = $isRoot 
                                        ? 'class="flex flex-col gap-1 w-full"' 
                                        : 'class="pl-4 border-l border-gray-200 ml-4 flex flex-col gap-1.5 mt-1 mb-1.5" x-show="open" x-transition';

                                    echo '<div ' . $containerAttr . '>';

                                    foreach ($cats as $cat) {
                                        $hasChildren = $categoriesGrouped->has($cat->id);
                                        $isActive = isset($currentCategory) && $currentCategory->id === $cat->id;
                                        
                                        // 判斷當前選中的分類是否在此節點的分支上（本身或是其子孫節點）
                                        $isBranchActive = isset($currentCategory) && ($isActive || $isDescendant($currentCategory->id, $cat->id));

                                        $url = lroute('category.show', ['slug' => $cat->slug ?: (collect($cat->getTranslations('slug'))->first() ?: 'category')]);
                                        
                                        // 依據是否為根層級（一級）調整字體與間距
                                        $padding = $isRoot ? 'px-4 py-2.5 rounded-lg' : 'px-3 py-1.5 rounded-md';
                                        $fontSize = $isRoot ? 'text-sm font-bold' : 'text-xs font-semibold';
                                        
                                        // 動態高亮樣式
                                        $activeClass = $isActive 
                                            ? 'bg-[#BE185D] text-white lg:bg-[#fdf2f8] lg:text-[#BE185D]' 
                                            : 'bg-white text-gray-700 hover:bg-gray-50 hover:text-[#BE185D] border-gray-200';

                                        // 使用 x-data 控制當前節點的展開摺疊狀態，預設展開處於活動分支的選單
                                        $xDataStr = 'x-data="{ open: ' . ($isBranchActive ? 'true' : 'false') . ' }"';

                                        echo '<div ' . $xDataStr . ' class="flex flex-col w-full">';
                                        
                                        // 輸出主列（將文字與箭頭分離，實現獨立操作）
                                        echo '  <div class="' . $padding . ' flex items-center justify-between gap-2 transition duration-200 border lg:border-none ' . $activeClass . ' group">';
                                        
                                        // 1. 分類名稱文字：點擊正常跳轉頁面載入商品
                                        echo '    <a href="' . $url . '" class="flex-grow truncate py-0.5 ' . $fontSize . '" title="' . e($cat->name) . '">';
                                        echo '      ' . e($cat->name);
                                        echo '    </a>';
                                        
                                        // 2. 右側小箭頭：點擊僅切換展開/摺疊狀態，不觸發跳轉，並帶有精美旋轉動畫
                                        if ($hasChildren) {
                                            echo '    <button @click.prevent.stop="open = !open" class="p-1 rounded hover:bg-gray-200/50 dark:hover:bg-gray-800/50 transition-colors duration-150 flex items-center justify-center focus:outline-none" style="margin-right: -4px;">';
                                            echo '      <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? \'rotate-90 text-[#BE185D]\' : \'\'" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                                            echo '          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>';
                                            echo '      </svg>';
                                            echo '    </button>';
                                        }
                                        
                                        echo '  </div>';

                                        // 遞迴渲染子層級
                                        if ($hasChildren) {
                                            $renderCategoryTree($cat->id);
                                        }

                                        echo '</div>';
                                    }

                                    echo '</div>';
                                };

                                // 執行根節點的遞迴渲染（渲染 parent_id 為 null 的分類）
                                $renderCategoryTree(null);
                            @endphp
                        </div>
                        </div>
                    </div>
                </aside>

                {{-- Product Grid Content --}}
                <main class="flex-grow lg:w-3/4">
                    {{-- Product Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($products as $product)
                        <article class="ind-card rounded-lg overflow-hidden group">
                            @php $primaryImg = $product->primaryImage ?? $product->images->first(); @endphp
                            <div class="Ind-image-container overflow-hidden">
                                @if($primaryImg)
                                <img src="{{ asset('storage/' . $primaryImg->paths['thumb']) }}"
                                     alt="{{ $product->meta_keywords ?? $product->name }}"
                                     class="w-full h-48 object-cover group-hover:scale-105 transition duration-300"
                                     loading="lazy"
                                     width="500"
                                     height="500">
                                @else
                                <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                @endif
                            </div>
                            <div class="p-5">
                                <h2 class="text-lg font-bold text-[#1a1a1a] mb-2 line-clamp-1">
                                    {{ $product->name }}
                                </h2>
                                <p class="text-gray-500 text-sm mb-3 line-clamp-2">
                                    {{ Str::limit(strip_tags($product->description), 60) }}
                                </p>
                                <div class="flex items-center justify-between">
                                    <a href="{{ lroute('products.show', ['id' => $product->id, 'slug' => $product->slug ?: 'product']) }}"
                                       class="text-[#1a1a1a] hover:text-[#BE185D] text-sm font-bold transition">
                                        {{ __('frontend.product.details') }} →
                                    </a>
                                </div>
                            </div>
                        </article>
                        @empty
                        <div class="col-span-full text-center py-16 text-gray-400">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <p>{{ __('frontend.product.no_products') }}</p>
                        </div>
                        @endforelse
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-12">
                        {{ $products->links() }}
                    </div>
                </main>
            </div>
        @endif
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
        ['@type' => 'ListItem', 'position' => 2, 'name' => $currentCategory ? $currentCategory->name : __('frontend.product.title')],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
