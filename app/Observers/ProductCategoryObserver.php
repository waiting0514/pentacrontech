<?php

namespace App\Observers;


use App\Models\Language;
use App\Models\ProductCategory;
use App\Services\SlugService;
use Illuminate\Support\Str;

class ProductCategoryObserver
{
    public function __construct(
        protected SlugService $slugService
    ) {}

    /**
     * Handle the ProductCategory "creating" event.
     */
    public function creating(ProductCategory $category): void
    {
        $this->generateSlugIfNeeded($category);
    }

    /**
     * Handle the ProductCategory "updating" event.
     */
    public function updating(ProductCategory $category): void
    {
        // System categories: protect slug and is_system fields
        if ($category->getOriginal('is_system')) {
            $category->is_system = true; // Prevent changing is_system
            $category->slug = $category->getOriginal('slug'); // Prevent changing slug
            return;
        }

        // Only regenerate slug if name changed and slug wasn't manually set
        if ($category->isDirty('name') && !$category->isDirty('slug')) {
            $this->generateSlugIfNeeded($category);
        } elseif ($category->isDirty('slug') && $category->slug) {
            // Sanitize manually provided slug
            $category->slug = Str::slug($category->slug);
        }
    }

    /**
     * Generate slug if not provided.
     */
    protected function generateSlugIfNeeded(ProductCategory $category): void
    {
        // If slug is provided, sanitize and ensure uniqueness
        if (!empty($category->slug)) {
            $baseSlug = Str::slug($category->slug);
            $category->slug = $this->ensureUniqueSlug($baseSlug, $category);
            return;
        }

        // Get name for slug generation (prefer zh, fallback to en)
        $name = $this->getNameForSlug($category);
        if (empty($name)) {
            return;
        }

        $category->slug = $this->slugService->generateSlug(
            $name,
            'product_categories',
            $category->id
        );
    }

    /**
     * Ensure slug is unique within the table.
     */
    protected function ensureUniqueSlug(string $slug, ProductCategory $category): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $category)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, ProductCategory $category): bool
    {
        $query = ProductCategory::where(function($q) use ($slug) {
                $q->where('slug', $slug)
                  ->orWhere('slug->zh-TW', $slug)
                  ->orWhere('slug->en', $slug)
                  ->orWhere('slug->zh', $slug);
            });

        if ($category->id) {
            $query->where('id', '!=', $category->id);
        }

        return $query->exists();
    }

    /**
     * Get the name to use for slug generation.
     */
    protected function getNameForSlug(ProductCategory $category): ?string
    {
        $translations = $category->getTranslations('name');
        $default = Language::defaultCode();
        if (isset($translations[$default]) && $translations[$default] !== '') {
            return $translations[$default];
        }
        if (isset($translations['en']) && $translations['en'] !== '') {
            return $translations['en'];
        }
        if (isset($translations['zh-TW']) && $translations['zh-TW'] !== '') {
            return $translations['zh-TW'];
        }
        if (!empty($translations)) {
            foreach ($translations as $val) {
                if ($val !== '' && $val !== null) {
                    return $val;
                }
            }
        }
        return $category->name ?? null;
    }
}
