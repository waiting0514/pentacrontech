<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Banner extends Model
{
    use HasTranslations;

    protected $fillable = [
        'title',
        'link_url',
        'image_pc',
        'image_mobile',
        'sort_order',
        'is_active',
    ];

    public $translatable = [
        'title',
        'link_url',
        'image_pc',
        'image_mobile',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get PC image URL with fallback to default locale
     */
    public function getPcImageUrlAttribute(): ?string
    {
        $path = $this->getTranslation('image_pc', app()->getLocale(), false);

        if (empty($path)) {
            $path = $this->getTranslation('image_pc', config('app.fallback_locale'), false);
        }

        return $path ? asset('storage/' . $path) : null;
    }

    /**
     * Get Mobile image URL with fallback to default locale
     */
    public function getMobileImageUrlAttribute(): ?string
    {
        $path = $this->getTranslation('image_mobile', app()->getLocale(), false);

        if (empty($path)) {
            $path = $this->getTranslation('image_mobile', config('app.fallback_locale'), false);
        }

        return $path ? asset('storage/' . $path) : null;
    }
}