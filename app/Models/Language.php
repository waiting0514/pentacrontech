<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Language extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saved(function ($language) {
            Cache::forget('active_languages');
            Cache::forget('default_language');
        });

        static::deleted(function ($language) {
            Cache::forget('active_languages');
            Cache::forget('default_language');
        });
    }

    /**
     * Get all active languages (Cached).
     */
    public static function getActive()
    {
        return Cache::rememberForever('active_languages', function () {
            return static::where('is_active', true)->get();
        });
    }

    /**
     * Get the default language (Cached).
     */
    public static function getDefault()
    {
        return Cache::rememberForever('default_language', function () {
            return static::where('is_active', true)
                ->where('is_default', true)
                ->first();
        });
    }
}