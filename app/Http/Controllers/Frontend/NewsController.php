<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewsController extends Controller
{
    /**
     * Display news listing.
     */
    public function index(): View
    {
        $site = Project::first();
        
        $news = News::published()
            ->latest('published_at')
            ->paginate(12);

        return view('news.index', [
            'site' => $site,
            'newsList' => $news,
            'metaTitle' => __('frontend.news.title'),
            'metaDescription' => __('frontend.news.stay_updated'),
        ]);
    }

    /**
     * Display news detail.
     */
    public function show(int $id, string $slug): View
    {
        $site = Project::first();
        
        $news = News::findOrFail($id);

        // Get related news
        $relatedNews = News::published()
            ->where('id', '!=', $id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('news.show', [
            'site' => $site,
            'news' => $news,
            'relatedNews' => $relatedNews,
            'metaTitle' => $news->meta_title ?: $news->title,
            'metaDescription' => $news->meta_description ?? '',
            'metaKeywords' => $news->meta_keywords ?? '',
            'ogImage' => $news->image_url,
            'ogType' => 'article',
        ]);
    }

    /**
     * Redirect to SEO-friendly URL.
     */
    public function redirectToSlug(int $id): RedirectResponse
    {
        $news = News::findOrFail($id);
        
        return redirect()->route('news.show', [
            'id' => $news->id,
            'slug' => $news->slug ?? 'news',
        ], 301);
    }
}
