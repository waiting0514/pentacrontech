<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class ProductAttachment extends Model
{
    use HasTranslations;

    protected $fillable = [
        'product_id',
        'title',
        'sort_order',
    ];

    public $translatable = [
        'title',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProductAttachmentFile::class, 'attachment_id');
    }

    /**
     * Title for a locale: current locale, then the default language, then any
     * non-empty title. Does not rely on Spatie's configurable fallback.
     */
    public function displayTitle(?string $locale = null): string
    {
        $titles = array_filter($this->getTranslations('title'), fn ($t) => filled($t));

        return $titles[$locale ?? app()->getLocale()]
            ?? $titles[Language::defaultCode()]
            ?? (reset($titles) ?: '');
    }

    /**
     * Resolve the file to serve for a locale: current locale, then the
     * default language, then any remaining file.
     */
    public function fileFor(?string $locale = null): ?ProductAttachmentFile
    {
        $locale ??= app()->getLocale();
        $files = $this->files->keyBy('locale');

        $default = Language::defaultCode();

        return $files->get($locale)
            ?? $files->get($default)
            ?? $files->first();
    }
}
