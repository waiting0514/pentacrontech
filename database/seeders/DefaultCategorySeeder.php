<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class DefaultCategorySeeder extends Seeder
{
    /**
     * Create default "Uncategorized" category for each project.
     */
    public function run(): void
    {
        $projects = Project::all();

        foreach ($projects as $project) {
            ProductCategory::firstOrCreate(
                [
                    'project_id' => $project->id,
                    'is_system' => true,
                ],
                [
                    'name' => ['zh-TW' => '未分類', 'en' => 'Uncategorized'],
                    'slug' => ['zh-TW' => 'uncategorized', 'en' => 'uncategorized'],
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );
        }
    }
}