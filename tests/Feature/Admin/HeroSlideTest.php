<?php

use App\Models\HeroSlide;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function heroAdmin(): User
{
    test()->seed(RolePermissionSeeder::class);
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    return $admin;
}

/**
 * @return array<string, mixed>
 */
function heroPayload(array $overrides = []): array
{
    return [
        'badge' => 'Promo',
        'title' => 'Judul Hero Baru',
        'highlight' => 'Sorotan Baru',
        'description' => 'Deskripsi hero baru.',
        'button_text' => 'Lihat produk',
        'button_url' => '/produk',
        'sort_order' => 1,
        'is_active' => 1,
        'image' => UploadedFile::fake()->image('hero.jpg', 1600, 800),
        ...$overrides,
    ];
}

it('blocks guests and customers from the hero module', function () {
    $this->seed(RolePermissionSeeder::class);
    $customer = User::factory()->create();
    $customer->assignRole('Customer');

    $this->get(route('admin.hero-slides.index'))->assertRedirect(route('login'));
    $this->actingAs($customer)->get(route('admin.hero-slides.index'))->assertForbidden();
    $this->actingAs($customer)->post(route('admin.hero-slides.store'), heroPayload())->assertForbidden();
});

it('lets an admin create a hero slide with an uploaded image', function () {
    Storage::fake('public');

    $this->actingAs(heroAdmin())
        ->post(route('admin.hero-slides.store'), heroPayload())
        ->assertRedirect(route('admin.hero-slides.index'));

    $slide = HeroSlide::query()->firstOrFail();
    expect($slide->title)->toBe('Judul Hero Baru');
    Storage::disk('public')->assertExists($slide->image_path);
});

it('validates hero slide input including unsafe button links', function () {
    Storage::fake('public');

    $this->actingAs(heroAdmin())
        ->post(route('admin.hero-slides.store'), heroPayload([
            'title' => '',
            'button_url' => 'javascript:alert(1)',
            'image' => null,
        ]))
        ->assertSessionHasErrors(['title', 'button_url', 'image']);

    expect(HeroSlide::query()->count())->toBe(0);
});

it('replaces the stored image only when a new one is uploaded', function () {
    Storage::fake('public');
    $admin = heroAdmin();
    $this->actingAs($admin)->post(route('admin.hero-slides.store'), heroPayload());
    $slide = HeroSlide::query()->firstOrFail();
    $oldPath = $slide->image_path;

    $this->put(route('admin.hero-slides.update', $slide), heroPayload(['title' => 'Diubah', 'image' => null]))
        ->assertRedirect(route('admin.hero-slides.index'));
    expect($slide->fresh()->title)->toBe('Diubah')
        ->and($slide->fresh()->image_path)->toBe($oldPath);

    $this->put(route('admin.hero-slides.update', $slide), heroPayload());
    $newPath = $slide->fresh()->image_path;
    expect($newPath)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($newPath);
});

it('toggles and deletes a hero slide', function () {
    Storage::fake('public');
    $slide = HeroSlide::factory()->create(['is_active' => true]);
    $admin = heroAdmin();

    $this->actingAs($admin)->patch(route('admin.hero-slides.toggle', $slide))
        ->assertRedirect(route('admin.hero-slides.index'));
    expect($slide->fresh()->is_active)->toBeFalse();

    $this->delete(route('admin.hero-slides.destroy', $slide))
        ->assertRedirect(route('admin.hero-slides.index'));
    expect(HeroSlide::query()->count())->toBe(0);
});

it('shows only active slides on the public page in order', function () {
    HeroSlide::factory()->create(['title' => 'Slide Kedua', 'sort_order' => 2]);
    HeroSlide::factory()->create(['title' => 'Slide Pertama', 'sort_order' => 1]);
    HeroSlide::factory()->create(['title' => 'Slide Tersembunyi', 'is_active' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder(['Slide Pertama', 'Slide Kedua'])
        ->assertDontSee('Slide Tersembunyi');
});

it('hides the hero section when there are no active slides', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('aria-label="Promo pilihan"', false);
});

it('renders the hero admin pages', function () {
    $slide = HeroSlide::factory()->create(['title' => 'Slide Terdaftar']);
    $admin = heroAdmin();

    $this->actingAs($admin)->get(route('admin.hero-slides.index'))
        ->assertOk()->assertSee('Slide Terdaftar');
    $this->get(route('admin.hero-slides.create'))->assertOk()->assertSee('Simpan slide');
    $this->get(route('admin.hero-slides.edit', $slide))->assertOk()->assertSee('Simpan perubahan');
});
