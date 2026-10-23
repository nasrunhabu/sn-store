<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = collect(['Super Admin', 'Admin', 'Customer'])
            ->mapWithKeys(fn (string $name): array => [
                $name => Role::findOrCreate($name, 'web'),
            ]);
        $manageRoles = Permission::findOrCreate('manage roles', 'web');

        $roles['Super Admin']->givePermissionTo($manageRoles);

        User::query()
            ->with('roles')
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($roles): void {
                foreach ($users as $user) {
                    if ($user->roles->isNotEmpty()) {
                        continue;
                    }

                    $user->assignRole(
                        (bool) $user->getAttribute('is_admin')
                            ? $roles['Admin']
                            : $roles['Customer']
                    );
                }
            });
    }
}
