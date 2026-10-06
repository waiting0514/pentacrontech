<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => ['zh-TW' => '電子產品', 'en' => 'Electronics'],
                'slug' => ['zh-TW' => 'electronics', 'en' => 'electronics'],
                'sort_order' => 10,
            ],
            [
                'name' => ['zh-TW' => '配件', 'en' => 'Accessories'],
                'slug' => ['zh-TW' => 'accessories', 'en' => 'accessories'],
                'sort_order' => 20,
            ],
            [
                'name' => ['zh-TW' => '軟體服務', 'en' => 'Software & Services'],
                'slug' => ['zh-TW' => 'software-services', 'en' => 'software-services'],
                'sort_order' => 30,
            ],
        ];

        foreach ($categories as $category) {
            ProductCategory::create($category);
        }

        $this->command->info('✓ 已建立 ' . count($categories) . ' 個產品分類');
    }
}