<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KnowledgeController extends Controller
{
    public function index(): View
    {
        $site = Project::first();

        $articleList = KnowledgeArticle::published()
            ->with('category')
            ->latest('published_at')
            ->paginate(12);

        return view('knowledge.index', [
            'site' => $site,
            'articleList' => $articleList,
            'metaTitle' => __('frontend.knowledge.title'),
            'metaDescription' => __('frontend.knowledge.subtitle'),
        ]);
    }

    public function category(string $categorySlug): View
    {
        $site = Project::first();
        $locale = app()->getLocale();

        $category = KnowledgeCategory::whereJsonContains('slug->' . $locale, $categorySlug)
            ->orWhereJsonContains('slug->zh-TW', $categorySlug)
            ->firstOrFail();

        $articleList = KnowledgeArticle::published()
            ->with('category')
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->paginate(12);

        return view('knowledge.category', [
            'site' => $site,
            'category' => $category,
            'articleList' => $articleList,
            'metaTitle' => $category->name . ' - ' . __('frontend.knowledge.title'),
            'metaDescription' => $category->description ?? __('frontend.knowledge.category_meta_description', ['name' => $category->name]),
        ]);
    }

    public function show(string $categorySlug, int $id, string $slug): View
    {
        $site = Project::first();

        $article = KnowledgeArticle::with('category')->findOrFail($id);

        $relatedArticles = KnowledgeArticle::published()
            ->with('category')
            ->where('id', '!=', $id)
            ->where(function ($q) use ($article) {
                $q->where('category_id', $article->category_id)
                  ->orWhere('schema_type', $article->schema_type);
            })
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('knowledge.show', [
            'site' => $site,
            'article' => $article,
            'relatedArticles' => $relatedArticles,
            'metaTitle' => $article->meta_title ?: $article->title,
            'metaDescription' => $article->meta_description ?? $article->excerpt ?? '',
            'metaKeywords' => $article->meta_keywords ?? '',
            'ogImage' => $article->image_url,
            'ogType' => 'article',
        ]);
    }

    public function redirectToSlug(string $categorySlug, int $id): RedirectResponse
    {
        $article = KnowledgeArticle::with('category')->findOrFail($id);
        $catSlug = $article->category?->slug ?? $categorySlug;

        return redirect()->route('knowledge.show', [
            'categorySlug' => $catSlug,
            'id' => $article->id,
            'slug' => $article->slug ?? 'article',
        ], 301);
    }
}
