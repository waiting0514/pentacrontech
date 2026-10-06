<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default Language: English
        Language::firstOrCreate(
            ['code' => 'en'],
            [
                'name' => 'English',
                'is_default' => true,
                'is_active' => true,
            ]
        );

        // Optional: Traditional Chinese
        Language::firstOrCreate(
            ['code' => 'zh-TW'],
            [
                'name' => '繁體中文',
                'is_default' => false,
                'is_active' => true,
            ]
        );
    }
}