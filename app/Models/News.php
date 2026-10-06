<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class News extends Model
{
    use HasTranslations;

    protected $fillable = [
        'project_id',
        'title',
        'slug',
        'content',
        'image_url',
        'category_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_published',
        'published_at',
    ];

    public $translatable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($news) {
            // Auto-set published_at if is_published is true but published_at is null
            if ($news->is_published && !$news->published_at) {
                $news->published_at = now();
            }
        });
    }

    /**
     * Get the category that owns the news.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class);
    }

    /**
     * Get the project that owns the news.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope for published news.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    /**
     * Get the URL for this news item.
     */
    public function getUrlAttribute(): string
    {
        return lroute('news.show', ['id' => $this->id, 'slug' => $this->slug ?? 'news']);
    }
}