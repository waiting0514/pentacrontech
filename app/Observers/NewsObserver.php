<?php

namespace App\Observers;


use App\Models\Language;
use App\Models\News;
use App\Services\SlugService;
use Illuminate\Support\Str;

class NewsObserver
{
    public function __construct(
        protected SlugService $slugService
    ) {}

    public function creating(News $news): void
    {
        $this->generateSlugIfNeeded($news);
    }

    public function updating(News $news): void
    {
        if ($news->isDirty('title') && !$news->isDirty('slug')) {
            $this->generateSlugIfNeeded($news);
        } elseif ($news->isDirty('slug') && $news->slug) {
            $news->slug = Str::slug($news->slug);
        }
    }

    protected function generateSlugIfNeeded(News $news): void
    {
        if (!empty($news->slug)) {
            $baseSlug = Str::slug($news->slug);
            $news->slug = $this->ensureUniqueSlug($baseSlug, $news);
            return;
        }

        $title = $this->getTitleForSlug($news);
        if (empty($title)) {
            return;
        }

        $news->slug = $this->slugService->generateSlug(
            $title,
            'news',
            $news->id
        );
    }

    protected function ensureUniqueSlug(string $slug, News $news): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $news)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, News $news): bool
    {
        $query = News::where('slug', $slug);

        if ($news->id) {
            $query->where('id', '!=', $news->id);
        }

        return $query->exists();
    }

    protected function getTitleForSlug(News $news): ?string
    {
        $translations = $news->getTranslations('title');
        return $translations[Language::defaultCode()] ?? $translations['en'] ?? $translations['zh-TW'] ?? $news->title ?? null;
    }
}
