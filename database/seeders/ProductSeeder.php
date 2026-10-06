<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $project = Project::first();
        $category = ProductCategory::where('is_system', false)->first();

        $products = [
            [
                'project_id' => $project->id,
                'category_id' => $category->id,
                'name' => ['zh-TW' => '高效能工業機器人', 'en' => 'High Performance Industrial Robot'],
                'slug' => ['zh-TW' => 'industrial-robot', 'en' => 'industrial-robot'], // 這裡可以不同，如 'robot-cn'
                'description' => ['zh-TW' => '適用於自動化生產線的高效能機器人。', 'en' => 'High performance robot for automated production lines.'],
                'price' => 50000,
                'is_inquiry_only' => true,
            ],
            [
                'project_id' => $project->id,
                'category_id' => $category->id,
                'name' => ['zh-TW' => '精密感測器', 'en' => 'Precision Sensor'],
                'slug' => ['zh-TW' => 'precision-sensor', 'en' => 'precision-sensor'],
                'description' => ['zh-TW' => '高精度、低延遲的工業感測器。', 'en' => 'High precision, low latency industrial sensor.'],
                'price' => 1200,
                'is_inquiry_only' => false,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}