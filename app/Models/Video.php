<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Video extends Model
{
    use HasTranslations;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'youtube_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'sort_order',
        'is_active',
    ];

    public $translatable = [
        'title',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $attributes = [
        'sort_order' => 0,
        'is_active' => true,
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getYoutubeUrlAttribute(): string
    {
        return 'https://www.youtube.com/watch?v='.$this->youtube_id;
    }

    public function getEmbedUrlAttribute(): string
    {
        return 'https://www.youtube-nocookie.com/embed/'.$this->youtube_id;
    }

    public function getThumbnailUrlAttribute(): string
    {
        return 'https://img.youtube.com/vi/'.$this->youtube_id.'/hqdefault.jpg';
    }

    public function getUrlAttribute(): string
    {
        return lroute('videos.show', ['id' => $this->id, 'slug' => $this->slug ?: 'video']);
    }
}
