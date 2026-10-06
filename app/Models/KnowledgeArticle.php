<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class KnowledgeArticle extends Model
{
    use HasTranslations;

    protected $fillable = [
        'project_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'image_url',
        'schema_type',
        'reading_time',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_published',
        'published_at',
    ];

    public $translatable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'reading_time' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($article) {
            if ($article->is_published && !$article->published_at) {
                $article->published_at = now();
            }

            // Auto-calculate reading_time from all language content
            $allContent = collect($article->getTranslations('content'))
                ->map(fn($c) => strip_tags($c ?? ''))
                ->implode(' ');
            $wordCount = str_word_count($allContent);
            $article->reading_time = max(1, (int) ceil($wordCount / 200));
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'category_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function getUrlAttribute(): string
    {
        $categorySlug = $this->category?->slug ?: 'general';
        return lroute('knowledge.show', [
            'categorySlug' => $categorySlug,
            'id' => $this->id,
            'slug' => $this->slug ?: 'article',
        ]);
    }
}
