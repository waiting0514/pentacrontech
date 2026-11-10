<?php

namespace App\Observers;


use App\Models\Language;
use App\Models\Product;
use App\Services\SlugService;
use Illuminate\Support\Str;

class ProductObserver
{
    public function __construct(
        protected SlugService $slugService
    ) {}

    /**
     * Handle the Product "creating" event.
     */
    public function creating(Product $product): void
    {
        $this->generateSlugIfNeeded($product);
    }

    /**
     * Handle the Product "updating" event.
     */
    public function updating(Product $product): void
    {
        // Only regenerate slug if name changed and slug wasn't manually set
        if ($product->isDirty('name') && !$product->isDirty('slug')) {
            $this->generateSlugIfNeeded($product);
        } elseif ($product->isDirty('slug') && $product->slug) {
            // Sanitize manually provided slug
            $product->slug = Str::slug($product->slug);
        }
    }

    /**
     * Generate slug if not provided.
     */
    protected function generateSlugIfNeeded(Product $product): void
    {
        // If slug is provided, sanitize and ensure uniqueness
        if (!empty($product->slug)) {
            $baseSlug = Str::slug($product->slug);
            $product->slug = $this->ensureUniqueSlug($baseSlug, $product);
            return;
        }

        // Get name for slug generation (prefer zh, fallback to en)
        $name = $this->getNameForSlug($product);
        if (empty($name)) {
            return;
        }

        $product->slug = $this->slugService->generateSlug(
            $name,
            'products',
            $product->id
        );
    }

    /**
     * Ensure slug is unique within the table.
     */
    protected function ensureUniqueSlug(string $slug, Product $product): string
    {
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $product)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists in the products table.
     */
    protected function slugExists(string $slug, Product $product): bool
    {
        $query = Product::where(function($q) use ($slug) {
                $q->where('slug', $slug)
                  ->orWhere('slug->zh-TW', $slug)
                  ->orWhere('slug->en', $slug)
                  ->orWhere('slug->zh', $slug);
            });

        if ($product->id) {
            $query->where('id', '!=', $product->id);
        }

        return $query->exists();
    }

    /**
     * Get the name to use for slug generation.
     */
    protected function getNameForSlug(Product $product): ?string
    {
        $translations = $product->getTranslations('name');
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
        return $product->name ?? null;
    }
}
