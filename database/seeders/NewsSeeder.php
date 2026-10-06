<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Categories
        $categories = [
            'company' => NewsCategory::create([
                'name' => ['zh-TW' => '公司公告', 'en' => 'Company News'],
                'slug' => ['zh-TW' => 'company-news', 'en' => 'company-news'],
                'sort_order' => 10,
            ]),
            'product' => NewsCategory::create([
                'name' => ['zh-TW' => '產品更新', 'en' => 'Product Updates'],
                'slug' => ['zh-TW' => 'product-updates', 'en' => 'product-updates'],
                'sort_order' => 20,
            ]),
            'industry' => NewsCategory::create([
                'name' => ['zh-TW' => '產業動態', 'en' => 'Industry Trends'],
                'slug' => ['zh-TW' => 'industry-trends', 'en' => 'industry-trends'],
                'sort_order' => 30,
            ]),
            'event' => NewsCategory::create([
                'name' => ['zh-TW' => '活動資訊', 'en' => 'Events'],
                'slug' => ['zh-TW' => 'events', 'en' => 'events'],
                'sort_order' => 40,
            ]),
        ];

        // 2. Create News
        $newsItems = [
            [
                'title' => ['zh-TW' => '公司成立十週年慶', 'en' => 'Company 10th Anniversary'],
                'slug' => ['zh-TW' => 'company-10th-anniversary', 'en' => 'company-10th-anniversary'],
                'content' => ['zh-TW' => '感謝各位客戶多年來的支持，我們即將迎來成立十週年的重要里程碑。', 'en' => 'Thank you for your support over the years. We are about to reach the milestone of our 10th anniversary.'],
                'category_id' => $categories['company']->id,
                'is_published' => true,
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => ['zh-TW' => '新產品系列即將上市', 'en' => 'New Product Line Coming Soon'],
                'slug' => ['zh-TW' => 'new-product-line-2026', 'en' => 'new-product-line-2026'],
                'content' => ['zh-TW' => '我們將於下個月推出全新的產品系列，敬請期待！', 'en' => 'We will launch a brand new product line next month. Stay tuned!'],
                'category_id' => $categories['product']->id,
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => ['zh-TW' => '春節假期公告', 'en' => 'Chinese New Year Holiday Notice'],
                'slug' => ['zh-TW' => 'cny-holiday-2026', 'en' => 'cny-holiday-2026'],
                'content' => ['zh-TW' => '春節假期為 1/28 - 2/4，期間暫停出貨，2/5 恢復正常營業。', 'en' => 'Chinese New Year holiday: 1/28 - 2/4. Shipping will resume on 2/5.'],
                'category_id' => $categories['company']->id,
                'is_published' => true,
                'published_at' => now()->subDays(7),
            ],
        ];

        foreach ($newsItems as $item) {
            News::create($item);
        }

        $this->command->info('✓ 已建立 ' . count($categories) . ' 個分類');
        $this->command->info('✓ 已建立 ' . count($newsItems) . ' 則新聞');
    }
}