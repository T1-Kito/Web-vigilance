<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allPermissions = Permission::query()->pluck('slug')->all();

        $user = User::updateOrCreate(
            ['email' => 'admin@webcn.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('123456'),
                'role' => 'admin',
                'permissions' => $allPermissions,
            ]
        );

        $this->command->info('Admin user ready successfully!');
        $this->command->info('Email: admin@webcn.com');
        $this->command->info('Password: 123456');
        $this->command->info('Permissions assigned: ' . count($allPermissions));
    }
}
