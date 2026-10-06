<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'title' => [
                    'zh-TW' => '歡迎來到 AI Agency',
                    'en' => 'Welcome to AI Agency'
                ],
                'link_url' => [
                    'zh-TW' => '/zh-TW/products',
                    'en' => '/en/products'
                ],
                'image_pc' => [
                    'zh-TW' => 'banners/pc-welcome-zh.jpg',
                    'en' => 'banners/pc-welcome-en.jpg'
                ],
                'image_mobile' => [
                    'zh-TW' => 'banners/mobile-welcome-zh.jpg',
                    'en' => 'banners/mobile-welcome-en.jpg'
                ],
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'title' => [
                    'zh-TW' => '最新產品上市',
                    'en' => 'New Arrivals'
                ],
                'link_url' => [
                    'zh-TW' => '/zh-TW/news',
                    'en' => '/en/news'
                ],
                // 故意留空英文圖片測試 Fallback
                'image_pc' => [
                    'zh-TW' => 'banners/pc-news-zh.jpg',
                ],
                'image_mobile' => [
                    'zh-TW' => 'banners/mobile-news-zh.jpg',
                ],
                'sort_order' => 20,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $data) {
            Banner::create($data);
        }

        $this->command->info('✓ 已建立 ' . count($banners) . ' 個輪播圖');
    }
}
