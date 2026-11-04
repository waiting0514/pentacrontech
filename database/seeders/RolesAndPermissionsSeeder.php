<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'product.view',
            'product.create',
            'product.update',
            'product.delete',
            'news.view',
            'news.create',
            'news.update',
            'news.delete',
            'knowledge.view',
            'knowledge.create',
            'knowledge.update',
            'knowledge.delete',
            'banner.manage',
            'video.manage',
            'ai.use',
            'user.manage',
            'report.view_cost',
            'system.manage',
            'order.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);

        // Frontend customer role (no backend permissions)
        Role::firstOrCreate(['name' => 'customer']);
        // super-admin bypasses all checks via Gate::before, no need to assign permissions

        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'product.view', 'product.create', 'product.update', 'product.delete',
            'news.view', 'news.create', 'news.update', 'news.delete',
            'knowledge.view', 'knowledge.create', 'knowledge.update', 'knowledge.delete',
            'banner.manage', 'video.manage', 'ai.use', 'user.manage', 'report.view_cost',
            'order.view',
        ]);

        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editor->syncPermissions([
            'product.view', 'product.create', 'product.update',
            'news.view', 'news.create', 'news.update', 'news.delete',
            'knowledge.view', 'knowledge.create', 'knowledge.update', 'knowledge.delete',
            'banner.manage', 'video.manage', 'ai.use',
        ]);

        // Create default users
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'username' => 'admin',
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('super-admin');

        // 示範帳號只在非正式環境建立；正式環境的其他使用者由後台新增並指定角色
        if (app()->isProduction()) {
            return;
        }

        $managerUser = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'username' => 'manager',
                'name' => 'Manager',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $managerUser->assignRole('manager');

        // Assign roles to existing users without roles
        User::whereDoesntHave('roles')->each(function ($user) {
            $user->assignRole('editor');
        });
    }
}
