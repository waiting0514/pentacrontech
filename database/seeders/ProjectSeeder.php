<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if project already exists
        if (Project::count() > 0) {
            $this->command->info('Projects already exist, skipping...');
            return;
        }

        // Create default project
        Project::create([
            'name' => 'AI Agency',
            'domain' => 'localhost',
            'yearly_budget_twd' => 1000,
            'meta_title' => 'AI Agency - 智能內容管理平台',
            'meta_description' => '提供 AI 驅動的內容管理解決方案，包含產品管理、新聞發布、AI 文字與圖片生成等功能。',
            'meta_keywords' => 'AI, 內容管理, CMS, 人工智慧, 自動化',
            'maintenance_mode' => false,
            'maintenance_message' => '系統維護中，預計 30 分鐘後恢復服務。',
            'contact_phone' => '+886-2-1234-5678',
            'contact_email' => 'info@aiagency.com',
            'contact_address' => '台北市信義區信義路五段7號',
        ]);

        $this->command->info('Default project created successfully!');
    }
}
