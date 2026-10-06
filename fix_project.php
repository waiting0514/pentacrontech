<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Project;

echo "--- Check Default Project ---\n";

$project = Project::find(1);

if ($project) {
    echo "SUCCESS: Project ID 1 found: " . $project->name . "\n";
} else {
    echo "WARNING: Project ID 1 NOT FOUND.\n";
    echo "Creating 'Main Site' project...\n";
    
    // Create specific ID 1 project
    $newProject = new Project();
    $newProject->id = 1;
    $newProject->name = 'Main Site';
    $newProject->domain = 'example.com';
    $newProject->monthly_budget_usd = 100.00;
    $newProject->is_active = true;
    $newProject->save();
    
    echo "SUCCESS: Created 'Main Site' (ID: 1).\n";
}

// List all projects
echo "\nCurrent Projects:\n";
foreach (Project::all() as $p) {
    echo "[ID: {$p->id}] {$p->name} ({$p->domain})\n";
}
echo "--------------------------\n";
