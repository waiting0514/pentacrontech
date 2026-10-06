<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class DefaultCategorySeeder extends Seeder
{
    /**
     * Create default "Uncategorized" category.
     */
    public function run(): void
    {
        ProductCategory::firstOrCreate(
            ['is_system' => true],
            [
                'name' => ['zh-TW' => '未分類', 'en' => 'Uncategorized'],
                'slug' => ['zh-TW' => 'uncategorized', 'en' => 'uncategorized'],
                'sort_order' => 0,
                'is_active' => true,
            ]
        );
    }
}
