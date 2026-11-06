<?php

namespace App\Services;

use App\Models\HeroSlide;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class HeroSlideService
{
    /**
     * @return Collection<int, HeroSlide>
     */
    public function getSlides(): Collection
    {
        return HeroSlide::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return Collection<int, HeroSlide>
     */
    public function getActiveSlides(): Collection
    {
        return HeroSlide::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{title: string, text: string, icon: string}
     */
    public function create(array $attributes, UploadedFile $image): array
    {
        $imagePath = $this->storeImage($image);

        try {
            $slide = HeroSlide::create([
                ...Arr::except($attributes, ['image']),
                'is_active' => (bool) ($attributes['is_active'] ?? false),
                'image_path' => $imagePath,
            ]);
        } catch (Throwable $exception) {
            $this->deleteStoredImage($imagePath);

            throw $exception;
        }

        return [
            'title' => 'Slide hero berhasil ditambahkan',
            'text' => "Slide \"{$slide->title}\" berhasil ditambahkan.",
            'icon' => 'success',
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{title: string, text: string, icon: string}
     */
    public function update(HeroSlide $slide, array $attributes, ?UploadedFile $image): array
    {
        $newImagePath = $image !== null ? $this->storeImage($image) : null;
        $oldImagePath = $slide->image_path;

        try {
            DB::transaction(fn () => $slide->update([
                ...Arr::except($attributes, ['image']),
                'is_active' => (bool) ($attributes['is_active'] ?? false),
                'image_path' => $newImagePath ?? $oldImagePath,
            ]));
        } catch (Throwable $exception) {
            if ($newImagePath !== null) {
                $this->deleteStoredImage($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath !== null) {
            $this->deleteStoredImage($oldImagePath);
        }

        return [
            'title' => 'Slide hero berhasil diperbarui',
            'text' => "Slide \"{$slide->title}\" berhasil diperbarui.",
            'icon' => 'success',
        ];
    }

    /**
     * @return array{title: string, text: string, icon: string}
     */
    public function toggleActive(HeroSlide $slide): array
    {
        $slide->update(['is_active' => ! $slide->is_active]);

        return [
            'title' => 'Status slide diperbarui',
            'text' => "Slide \"{$slide->title}\" berhasil ".($slide->is_active ? 'diaktifkan.' : 'dinonaktifkan.'),
            'icon' => 'success',
        ];
    }

    /**
     * @return array{title: string, text: string, icon: string}
     */
    public function delete(HeroSlide $slide): array
    {
        $title = $slide->title;
        $imagePath = $slide->image_path;

        DB::transaction(fn () => $slide->delete());
        $this->deleteStoredImage($imagePath);

        return [
            'title' => 'Slide hero berhasil dihapus',
            'text' => "Slide \"{$title}\" berhasil dihapus.",
            'icon' => 'success',
        ];
    }

    private function storeImage(UploadedFile $image): string
    {
        $path = $image->store('hero', 'public');

        if (! is_string($path)) {
            throw new RuntimeException('Gambar hero gagal disimpan.');
        }

        return $path;
    }

    /**
     * URL eksternal (data bawaan) tidak ada di disk sehingga dilewati.
     */
    private function deleteStoredImage(string $imagePath): void
    {
        if (Str::startsWith($imagePath, ['http://', 'https://'])) {
            return;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($imagePath) && ! $disk->delete($imagePath)) {
            throw new RuntimeException('Gambar hero gagal dihapus.');
        }
    }
}
