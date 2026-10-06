#!/usr/bin/env php
<?php

// 快速驗證腳本 - 檢查測試使用者是否存在

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== 檢查測試使用者 ===\n\n";

$testUser = User::where('email', 'test@example.com')->first();
$adminUser = User::where('email', 'admin@example.com')->first();

if ($testUser) {
    echo "✓ 測試使用者已建立\n";
    echo "  Email: test@example.com\n";
    echo "  Name: {$testUser->name}\n";
    echo "  Budget: \${$testUser->monthly_budget_usd}\n\n";
} else {
    echo "✗ 測試使用者不存在\n\n";
}

if ($adminUser) {
    echo "✓ 管理員已建立\n";
    echo "  Email: admin@example.com\n";
    echo "  Name: {$adminUser->name}\n";
    echo "  Budget: \${$adminUser->monthly_budget_usd}\n\n";
} else {
    echo "✗ 管理員不存在\n\n";
}

echo "總使用者數: " . User::count() . "\n\n";

if ($testUser) {
    echo "=== 登入資訊 ===\n";
    echo "URL: http://localhost\n";
    echo "Email: test@example.com\n";
    echo "Password: password\n";
}
