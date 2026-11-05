<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasTranslations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'specs',
        'price',
        'is_inquiry_only',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public $translatable = [
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function attachments()
    {
        return $this->hasMany(ProductAttachment::class)->orderBy('sort_order')->orderBy('id');
    }

    protected static function booted(): void
    {
        // FK cascade removes rows without firing model events, so delete the
        // file records first to clean up the physical PDFs.
        static::deleting(function (Product $product) {
            $product->attachments()->with('files')->get()
                ->each(fn (ProductAttachment $a) => $a->files->each->delete());
        });
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'is_inquiry_only' => 'boolean',
    ];
}
