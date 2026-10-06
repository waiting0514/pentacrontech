<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use App\Models\News;
use App\Models\Project;
use App\Models\ProductCategory;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the homepage.
     */
    public function index(): View
    {
        // Get site settings (default project)
        $site = Project::first();

        // Get active banners
        $banners = Banner::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        // Get featured categories (active, up to 3)
        $featuredCategories = ProductCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->take(3)
            ->get();

        // Get featured products (latest 3)
        $featuredProducts = Product::with(['primaryImage', 'images'])->latest()
            ->take(3)
            ->get();

        // Get latest news (published, latest 3)
        $latestNews = News::published()
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('pages.home', [
            'site' => $site,
            'banners' => $banners,
            'featuredCategories' => $featuredCategories,
            'featuredProducts' => $featuredProducts,
            'latestNews' => $latestNews,
            'metaTitle' => $site?->meta_title,
            'metaDescription' => $site?->meta_description,
            'metaKeywords' => $site?->meta_keywords,
        ]);
    }
}
