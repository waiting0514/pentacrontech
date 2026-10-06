<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->getTranslations('title'),
            'link_url' => $this->getTranslations('link_url'),
            'image_pc' => $this->getTranslations('image_pc'),
            'image_mobile' => $this->getTranslations('image_mobile'),
            // 輔助屬性用於預覽
            'image_pc_url' => $this->pc_image_url,
            'image_mobile_url' => $this->mobile_image_url,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}