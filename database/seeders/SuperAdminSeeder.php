<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@sn-store.com'],
            [
                'name' => 'SN Store Super Admin',
                'password' => '12345678',
                'email_verified_at' => now(),
            ],
        );

        $superAdmin->syncRoles('Super Admin');
    }
}
