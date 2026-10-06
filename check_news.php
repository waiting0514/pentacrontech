<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Current time: " . now()->format('Y-m-d H:i:s') . "\n";
echo "Timezone: " . config('app.timezone') . "\n\n";

$news = App\Models\News::find(1);
echo "News published_at: " . ($news->published_at ? $news->published_at->format('Y-m-d H:i:s') : 'NULL') . "\n";
echo "Is future? " . ($news->published_at > now() ? 'YES (問題在這!)' : 'NO') . "\n\n";

// Fix it
echo "Updating published_at to now()...\n";
$news->published_at = now();
$news->save();

echo "New published_at: " . $news->published_at->format('Y-m-d H:i:s') . "\n";
echo "Published news count: " . App\Models\News::published()->count() . "\n";
