<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Projects Table ===\n";

try {
    $count = App\Models\Project::count();
    echo "Projects count: $count\n\n";
    
    if ($count === 0) {
        echo "❌ No projects found! Creating default project...\n";
        
        $project = App\Models\Project::create([
            'name' => 'AI Agency',
            'domain' => 'localhost',
            'monthly_budget_usd' => 100,
            'meta_title' => 'AI Agency - 智能內容管理平台',
            'meta_description' => '提供 AI 驅動的內容管理解決方案',
            'meta_keywords' => 'AI, 內容管理, CMS',
        ]);
        
        echo "✅ Default project created!\n";
        echo "ID: {$project->id}\n";
        echo "Name: {$project->name}\n";
    } else {
        echo "✅ Projects exist:\n";
        $projects = App\Models\Project::all();
        foreach ($projects as $p) {
            echo "  - ID: {$p->id}, Name: {$p->name}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
