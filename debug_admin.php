<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

echo "--- Admin User Debug ---\n";

// 1. Check if users table exists
if (!Schema::hasTable('users')) {
    echo "ERROR: 'users' table does not exist. Migrations might not have run correctly.\n";
    exit(1);
}

// 2. Find admin user
$admin = User::where('email', 'admin@example.com')->first();

if (!$admin) {
    echo "ERROR: User 'admin@example.com' NOT FOUND in database.\n";
    echo "Did you run the seeder? (php artisan db:seed --class=AdminSeeder)\n";
} else {
    echo "SUCCESS: User found.\n";
    echo "ID: " . $admin->id . "\n";
    echo "Name: " . $admin->name . "\n";
    echo "Email: " . $admin->email . "\n";
    
    // 3. Verify password
    if (Hash::check('admin123', $admin->password)) {
        echo "SUCCESS: Password 'admin123' matches the hash.\n";
    } else {
        echo "ERROR: Password 'admin123' DOES NOT match the stored hash.\n";
        echo "Updating password to 'admin123'...\n";
        $admin->password = Hash::make('admin123');
        $admin->save();
        echo "Password updated.\n";
    }
}
echo "------------------------\n";
