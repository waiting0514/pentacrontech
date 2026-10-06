<?php

namespace App\Observers;

use App\Models\KnowledgeCategory;
use App\Services\SlugService;
use Illuminate\Support\Str;

class KnowledgeCategoryObserver
{
    public function __construct(
        protected SlugService $slugService
    ) {}

    public function creating(KnowledgeCategory $category): void
    {
        $this->generateSlugIfNeeded($category);
    }

    public function updating(KnowledgeCategory $category): void
    {
        if ($category->isDirty('name') && !$category->isDirty('slug')) {
            $this->generateSlugIfNeeded($category);
        } elseif ($category->isDirty('slug') && $category->slug) {
            $category->slug = Str::slug($category->slug);
        }
    }

    protected function generateSlugIfNeeded(KnowledgeCategory $category): void
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
            'knowledge_categories',
            $category->id
        );
    }

    protected function ensureUniqueSlug(string $slug, KnowledgeCategory $category): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $category)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, KnowledgeCategory $category): bool
    {
        $query = KnowledgeCategory::where('slug', $slug);

        if ($category->id) {
            $query->where('id', '!=', $category->id);
        }

        return $query->exists();
    }

    protected function getNameForSlug(KnowledgeCategory $category): ?string
    {
        $translations = $category->getTranslations('name');
        return $translations['zh-TW'] ?? $translations['en'] ?? $category->name ?? null;
    }
}
