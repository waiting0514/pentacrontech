<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'title' => $this->getTranslations('title'),
            'sort_order' => $this->sort_order,
            'files' => $this->files->map(fn ($file) => [
                'locale' => $file->locale,
                'original_name' => $file->original_name,
                'size' => $file->size,
                'human_size' => $file->human_size,
                'url' => $file->url,
            ])->values(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
