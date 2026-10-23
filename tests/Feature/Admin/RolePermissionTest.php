<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

it('allows customers to browse the storefront but blocks admin pages', function () {
    Storage::fake('local');
    $this->seed(RolePermissionSeeder::class);
    $customer = User::factory()->create();
    $customer->assignRole('Customer');

    $this->actingAs($customer)
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('href="'.route('dashboard').'"');

    $this->get(route('dashboard'))->assertForbidden();
    $this->get(route('admin.categories.index'))->assertForbidden();
    $this->get(route('admin.roles.index'))->assertForbidden();
});

it('allows admins to use the dashboard but denies role management', function () {
    Storage::fake('local');
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Role & permission');

    $this->get(route('admin.products.index'))->assertOk();
    $this->get(route('admin.roles.index'))->assertForbidden();
});

it('blocks admins from role management even when they have the permission', function () {
    Storage::fake('local');
    $this->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');
    $admin->givePermissionTo(Permission::findByName('manage roles', 'web'));

    $this->actingAs($admin)
        ->get(route('admin.roles.index'))
        ->assertForbidden();
});

it('allows super admins to assign admin and customer roles', function () {
    Storage::fake('local');
    $this->seed(SuperAdminSeeder::class);
    $superAdmin = User::query()->where('email', 'admin@sn-store.com')->firstOrFail();
    $customer = User::factory()->create();
    $customer->assignRole('Customer');

    $this->actingAs($superAdmin)
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertSee('Role akun pengguna')
        ->assertSee('Dilindungi');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('dashboard'));

    $this->put(route('admin.roles.update', $customer), ['role' => 'Admin'])
        ->assertRedirect(route('admin.roles.index'));

    expect($customer->fresh()->hasRole('Admin'))->toBeTrue();
});

it('prevents super admins from assigning the super admin role or changing protected users', function () {
    Storage::fake('local');
    $this->seed(SuperAdminSeeder::class);
    $superAdmin = User::query()->where('email', 'admin@sn-store.com')->firstOrFail();
    $customer = User::factory()->create();
    $customer->assignRole('Customer');

    $this->actingAs($superAdmin)
        ->put(route('admin.roles.update', $customer), ['role' => 'Super Admin'])
        ->assertSessionHasErrors('role');

    $this->put(route('admin.roles.update', $superAdmin), ['role' => 'Admin'])
        ->assertForbidden();

    $otherSuperAdmin = User::factory()->create();
    $otherSuperAdmin->assignRole('Super Admin');

    $this->put(route('admin.roles.update', $otherSuperAdmin), ['role' => 'Customer'])
        ->assertForbidden();

    expect($customer->fresh()->hasRole('Customer'))->toBeTrue();
    expect($superAdmin->fresh()->hasRole('Super Admin'))->toBeTrue();
    expect($otherSuperAdmin->fresh()->hasRole('Super Admin'))->toBeTrue();
});

it('seeds the dedicated super admin account with the requested credentials', function () {
    $this->seed(SuperAdminSeeder::class);
    $superAdmin = User::query()->where('email', 'admin@sn-store.com')->firstOrFail();

    expect($superAdmin->hasRole('Super Admin'))->toBeTrue();
    expect(Hash::check('12345678', $superAdmin->password))->toBeTrue();
    expect($superAdmin->canAccessAdminPanel())->toBeTrue();
});
