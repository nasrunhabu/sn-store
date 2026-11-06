<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHeroSlideRequest;
use App\Http\Requests\Admin\UpdateHeroSlideRequest;
use App\Models\HeroSlide;
use App\Services\HeroSlideService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HeroSlideController extends Controller
{
    public function index(HeroSlideService $heroSlideService): View
    {
        return view('pages.admin.hero-slides.index', [
            'slides' => $heroSlideService->getSlides(),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.hero-slides.form', [
            'slide' => null,
            'pageTitle' => 'Tambah slide hero',
        ]);
    }

    public function store(StoreHeroSlideRequest $request, HeroSlideService $heroSlideService): RedirectResponse
    {
        return to_route('admin.hero-slides.index')
            ->with('hero_alert', $heroSlideService->create($request->validated(), $request->file('image')));
    }

    public function edit(HeroSlide $heroSlide): View
    {
        return view('pages.admin.hero-slides.form', [
            'slide' => $heroSlide,
            'pageTitle' => 'Edit slide hero',
        ]);
    }

    public function update(UpdateHeroSlideRequest $request, HeroSlide $heroSlide, HeroSlideService $heroSlideService): RedirectResponse
    {
        return to_route('admin.hero-slides.index')
            ->with('hero_alert', $heroSlideService->update($heroSlide, $request->validated(), $request->file('image')));
    }

    public function toggle(HeroSlide $heroSlide, HeroSlideService $heroSlideService): RedirectResponse
    {
        return to_route('admin.hero-slides.index')
            ->with('hero_alert', $heroSlideService->toggleActive($heroSlide));
    }

    public function destroy(HeroSlide $heroSlide, HeroSlideService $heroSlideService): RedirectResponse
    {
        return to_route('admin.hero-slides.index')
            ->with('hero_alert', $heroSlideService->delete($heroSlide));
    }
}
