<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class RagService
{
    public function searchProducts(string $query, int $limit = 5): Collection
    {
        return Product::where(function ($q) use ($query) {
                $q->where('name->zh-TW', 'like', "%{$query}%")
                  ->orWhere('name->en', 'like', "%{$query}%")
                  ->orWhere('description->zh-TW', 'like', "%{$query}%")
                  ->orWhere('description->en', 'like', "%{$query}%")
                  ->orWhere('specs', 'like', "%{$query}%");
            })
            ->take($limit)
            ->get();
    }
}
