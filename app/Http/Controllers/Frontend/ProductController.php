<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display product listing.
     */
    public function index(Request $request): View
    {
        $site = SiteSetting::current();
        $categories = ProductCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $query = Product::with(['primaryImage', 'images'])->latest();

        $currentCategory = null;
        if ($request->filled('category')) {
            $catSlug = $request->category;
            $currentCategory = ProductCategory::where(function($q) use ($catSlug) {
                $q->where('slug', $catSlug)
                  ->orWhere('slug->zh-TW', $catSlug)
                  ->orWhere('slug->en', $catSlug)
                  ->orWhere('slug->zh', $catSlug);
            })->first();
            if ($currentCategory) {
                $categoryIds = $this->getCategoryIdsRecursive($currentCategory);
                $query->whereIn('category_id', $categoryIds);
            }
        }

        $products = $query->paginate(12)->withQueryString();

        return view('products.index', [
            'site' => $site,
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $currentCategory,
            'metaTitle' => $currentCategory?->name ?: __('frontend.product.list'),
            'metaDescription' => __('frontend.product.explore_full_range'),
        ]);
    }

    /**
     * Display product detail.
     */
    public function show(int $id, string $slug): View
    {
        $site = SiteSetting::current();
        
        $product = Product::with(['primaryImage', 'images', 'attachments.files'])->findOrFail($id);

        // Get related products (exclude current)
        $relatedProducts = Product::with(['primaryImage', 'images'])->where('id', '!=', $id)
            ->latest()
            ->take(4)
            ->get();

        $metaTitle = $product->meta_title
            ?: ($site?->meta_title
            ?: ($product->name ?? ''));
        $metaDescription = $product->meta_description ?: ($site?->meta_description ?? '');
        $metaKeywords = $product->meta_keywords ?: ($site?->meta_keywords ?? '');

        return view('products.show', [
            'site' => $site,
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'metaKeywords' => $metaKeywords,
            'ogImage' => $product->image_url,
        ]);
    }

    /**
     * Redirect to SEO-friendly URL.
     */
    public function redirectToSlug(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);

        return redirect()->route('products.show', [
            'id' => $product->id,
            'slug' => $product->slug ?: 'product',
        ], 301);
    }

    /**
     * Display products by category (SEO-friendly URL).
     */
    public function category(string $slug): View
    {
        $site = SiteSetting::current();
        $category = ProductCategory::where(function($q) use ($slug) {
                $q->where('slug', $slug)
                  ->orWhere('slug->zh-TW', $slug)
                  ->orWhere('slug->en', $slug)
                  ->orWhere('slug->zh', $slug);
            })
            ->where('is_active', true)
            ->firstOrFail();

        $categories = ProductCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $categoryIds = $this->getCategoryIdsRecursive($category);

        $products = Product::with(['primaryImage', 'images'])
            ->whereIn('category_id', $categoryIds)
            ->latest()
            ->paginate(12);

        return view('products.index', [
            'site' => $site,
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $category,
            'metaTitle' => $category->name ?: __('frontend.product.list'),
            'metaDescription' => __('frontend.product.category_meta_description', ['name' => $category->name]),
        ]);
    }

    /**
     * 遞迴取得分類及其所有子孫分類的 ID
     */
    private function getCategoryIdsRecursive(ProductCategory $category): array
    {
        $ids = [$category->id];
        
        foreach ($category->children as $child) {
            $ids = array_merge($ids, $this->getCategoryIdsRecursive($child));
        }
        
        return $ids;
    }
}
