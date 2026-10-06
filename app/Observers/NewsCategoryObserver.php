<?php

namespace App\Observers;

use App\Models\NewsCategory;
use App\Services\SlugService;
use Illuminate\Support\Str;

class NewsCategoryObserver
{
    public function __construct(
        protected SlugService $slugService
    ) {}

    public function creating(NewsCategory $category): void
    {
        $this->generateSlugIfNeeded($category);
    }

    public function updating(NewsCategory $category): void
    {
        if ($category->isDirty('name') && !$category->isDirty('slug')) {
            $this->generateSlugIfNeeded($category);
        } elseif ($category->isDirty('slug') && $category->slug) {
            $category->slug = Str::slug($category->slug);
        }
    }

    protected function generateSlugIfNeeded(NewsCategory $category): void
    {
        if (!empty($category->slug)) {
            $baseSlug = Str::slug($category->slug);
            $category->slug = $this->ensureUniqueSlug($baseSlug, $category);
            return;
        }

        $name = $this->getNameForSlug($category);
        if (empty($name)) {
            return;
        }

        $category->slug = $this->slugService->generateSlug(
            $name,
            'news_categories',
            $category->id
        );
    }

    protected function ensureUniqueSlug(string $slug, NewsCategory $category): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $category)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, NewsCategory $category): bool
    {
        $query = NewsCategory::where('slug', $slug);

        if ($category->id) {
            $query->where('id', '!=', $category->id);
        }

        return $query->exists();
    }

    protected function getNameForSlug(NewsCategory $category): ?string
    {
        $translations = $category->getTranslations('name');
        return $translations['zh-TW'] ?? $translations['en'] ?? $category->name ?? null;
    }
}
