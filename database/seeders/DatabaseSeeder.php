<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,      // 建立語言設定
            ProjectSeeder::class,       // 先建立專案
            UserSeeder::class,          // 建立使用者（包含 admin）
            RolesAndPermissionsSeeder::class, // 建立角色與權限
            DefaultCategorySeeder::class, // 建立預設分類（未分類）
            ProductCategorySeeder::class, // 建立產品分類
            ProductSeeder::class,         // 建立產品資料
            NewsSeeder::class,            // 建立新聞
            BannerSeeder::class,          // 建立輪播圖
        ]);
    }
}
