<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Waste Reports
            'reports:view-any',
            'reports:view-own',
            'reports:create',
            'reports:update-own',
            'reports:delete-own',
            'reports:update-status',
            'reports:delete-any',

            // Waste Categories
            'categories:view',
            'categories:manage',

            // User Management
            'users:view',
            'users:manage',

            // Dashboard
            'dashboard:view-admin',
            'dashboard:view-user',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Roles
        $adminRole = Role::firstOrCreate(['name' => UserRole::Admin->value, 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => UserRole::User->value, 'guard_name' => 'web']);

        // Admin has all permissions
        $adminRole->syncPermissions(Permission::all());

        // Regular user permissions
        $userRole->syncPermissions([
            'reports:view-own',
            'reports:create',
            'reports:update-own',
            'reports:delete-own',
            'categories:view',
            'dashboard:view-user',
        ]);
    }
}

