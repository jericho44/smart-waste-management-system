<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = env('INITIAL_ADMIN_EMAIL', 'admin@waste.local');
        $adminPassword = env('INITIAL_ADMIN_PASSWORD', 'Admin@123456');

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'System Administrator',
                'password' => Hash::make($adminPassword),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if (! $admin->hasRole(UserRole::Admin->value)) {
            $admin->assignRole(UserRole::Admin->value);
        }

        // Create standard test citizen user
        $userEmail = 'user@waste.local';
        $user = User::firstOrCreate(
            ['email' => $userEmail],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Citizen John Doe',
                'password' => Hash::make('Password@123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if (! $user->hasRole(UserRole::User->value)) {
            $user->assignRole(UserRole::User->value);
        }
    }
}

