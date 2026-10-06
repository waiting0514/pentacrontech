<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing /api/projects/current ===\n";

try {
    $project = App\Models\Project::first();
    if ($project) {
        echo "✅ Project found:\n";
        echo "ID: {$project->id}\n";
        echo "Name: {$project->name}\n";
        echo "Domain: {$project->domain}\n";
        echo "Monthly Budget: {$project->monthly_budget_usd}\n";
    } else {
        echo "❌ No project found in database!\n";
        echo "Creating default project...\n";
        
        $project = App\Models\Project::create([
            'name' => 'AI Agency',
            'domain' => 'localhost',
            'monthly_budget_usd' => 100,
            'meta_title' => 'AI Agency - 智能內容管理平台',
            'meta_description' => '提供 AI 驅動的內容管理解決方案',
        ]);
        
        echo "✅ Default project created (ID: {$project->id})\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
