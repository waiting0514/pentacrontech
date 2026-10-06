<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslations('title'),
            'slug' => $this->getTranslations('slug'),
            'content' => $this->getTranslations('content'),
            'image_url' => $this->image_url,
            'category' => new NewsCategoryResource($this->whenLoaded('category')),
            'category_id' => $this->category_id,
            'meta_title' => $this->getTranslations('meta_title'),
            'meta_description' => $this->getTranslations('meta_description'),
            'meta_keywords' => $this->getTranslations('meta_keywords'),
            'is_published' => $this->is_published,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
