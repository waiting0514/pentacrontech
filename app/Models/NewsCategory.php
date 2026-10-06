<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class NewsCategory extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
        'is_active',
    ];

    public $translatable = [
        'name',
        'slug',
    ];

    protected $casts = [
        'name' => 'array',
        'slug' => 'array',
        'is_active' => 'boolean',
    ];

    public function news()
    {
        return $this->hasMany(News::class, 'category_id');
    }
}