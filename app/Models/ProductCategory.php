<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ProductCategory extends Model
{
    use HasTranslations;

    protected $fillable = [
        'project_id',
        'parent_id',
        'name',
        'slug',
        'sort_order',
        'is_active',
        'is_system',
        'image_path',
     ];

    public $translatable = [
        'name',
        'slug',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Get all nested child categories recursively.
     */
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Get the default "Uncategorized" category for a project.
     */
    public static function getDefaultCategory(?int $projectId = null): ?self
    {
        return static::where('project_id', $projectId)
            ->where('is_system', true)
            ->first();
    }

    /**
     * Check if this is a system-protected category.
     */
    public function isSystemCategory(): bool
    {
        return $this->is_system === true;
    }
}