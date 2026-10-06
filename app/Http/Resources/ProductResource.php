<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => new ProductCategoryResource($this->whenLoaded('category')),
            'name' => $this->getTranslations('name'),
            'description' => $this->getTranslations('description'),
            'specs' => $this->specs,
            'price' => $this->price,
            'is_inquiry_only' => (bool) $this->is_inquiry_only,
            'slug' => $this->getTranslations('slug'),
            'meta_title' => $this->getTranslations('meta_title'),
            'meta_description' => $this->getTranslations('meta_description'),
            'meta_keywords' => $this->getTranslations('meta_keywords'),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'paths' => [
                    'main' => $img->paths['main'] ? asset('storage/' . $img->paths['main']) : null,
                    'thumb' => $img->paths['thumb'] ? asset('storage/' . $img->paths['thumb']) : null,
                    'tiny' => $img->paths['tiny'] ? asset('storage/' . $img->paths['tiny']) : null,
                ],
                'is_primary' => $img->is_primary,
                'sort_order' => $img->sort_order,
            ])),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
