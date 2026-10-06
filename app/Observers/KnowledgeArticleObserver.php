<?php

namespace App\Observers;

use App\Models\KnowledgeArticle;
use App\Services\SlugService;
use Illuminate\Support\Str;

class KnowledgeArticleObserver
{
    public function __construct(
        protected SlugService $slugService
    ) {}

    public function creating(KnowledgeArticle $article): void
    {
        $this->generateSlugIfNeeded($article);
    }

    public function updating(KnowledgeArticle $article): void
    {
        if ($article->isDirty('title') && !$article->isDirty('slug')) {
            $this->generateSlugIfNeeded($article);
        } elseif ($article->isDirty('slug') && $article->slug) {
            $article->slug = Str::slug($article->slug);
        }
    }

    protected function generateSlugIfNeeded(KnowledgeArticle $article): void
    {
        if (!empty($article->slug)) {
            $baseSlug = Str::slug($article->slug);
            $article->slug = $this->ensureUniqueSlug($baseSlug, $article);
            return;
        }

        $title = $this->getTitleForSlug($article);
        if (empty($title)) {
            return;
        }

        $article->slug = $this->slugService->generateSlug(
            $title,
            'knowledge_articles',
            $article->id
        );
    }

    protected function ensureUniqueSlug(string $slug, KnowledgeArticle $article): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $article)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, KnowledgeArticle $article): bool
    {
        $query = KnowledgeArticle::where('slug', $slug);

        if ($article->id) {
            $query->where('id', '!=', $article->id);
        }

        return $query->exists();
    }

    protected function getTitleForSlug(KnowledgeArticle $article): ?string
    {
        $translations = $article->getTranslations('title');
        return $translations['zh-TW'] ?? $translations['en'] ?? $article->title ?? null;
    }
}
