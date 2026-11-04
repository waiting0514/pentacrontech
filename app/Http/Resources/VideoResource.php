<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslations('title'),
            'description' => $this->getTranslations('description'),
            'slug' => $this->slug,
            'youtube_id' => $this->youtube_id,
            'youtube_url' => $this->youtube_url,
            'embed_url' => $this->embed_url,
            'thumbnail_url' => $this->thumbnail_url,
            'meta_title' => $this->getTranslations('meta_title'),
            'meta_description' => $this->getTranslations('meta_description'),
            'meta_keywords' => $this->getTranslations('meta_keywords'),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
