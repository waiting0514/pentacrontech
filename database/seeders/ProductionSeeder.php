<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * 正式環境初始資料：只建立系統運作必要的資料，不含示範產品、新聞、輪播圖。
 * 執行：php artisan db:seed --class=ProductionSeeder --force
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            SiteSettingSeeder::class,
            UserSeeder::class,
            RolesAndPermissionsSeeder::class,
            DefaultCategorySeeder::class,
        ]);
    }
}
