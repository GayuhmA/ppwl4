<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectGalleryController;
use App\Http\Controllers\SkillController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');
Route::get('/about', fn () => view('about'))->name('about');
Route::get('/education', fn () => view('education'))->name('education');
Route::get('/projects', [PortfolioController::class, 'projects'])->name('portfolio.projects');
Route::get('/projects/{project}', [PortfolioController::class, 'show'])->name('portfolio.projects.show');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/post/profile-saya', function () {
        return view('profile-saya');
    })->name('profile-saya');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('post')->middleware(['auth', 'admin'])->group(function () {
    Route::post('projects/{project}/gallery', [ProjectGalleryController::class, 'store'])
        ->name('projects.gallery.store');
    Route::put('projects/{project}/gallery/{image}', [ProjectGalleryController::class, 'update'])
        ->name('projects.gallery.update');
    Route::delete('projects/{project}/gallery/{image}', [ProjectGalleryController::class, 'destroy'])
        ->name('projects.gallery.destroy');
    Route::resource('projects', ProjectController::class);
    Route::resource('skills', SkillController::class)->only(['index', 'edit', 'update']);
});

require __DIR__.'/auth.php';
