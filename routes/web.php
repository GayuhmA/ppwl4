<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home'))->name('home');
Route::get('/about', fn () => view('about'))->name('about');
Route::get('/education', fn () => view('education'))->name('education');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('profile-saya');
    })->name('dashboard');

    Route::get('/post/profile-saya', function () {
        return view('profile-saya');
    })->name('profile-saya');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('post')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('projects', ProjectController::class);
});

require __DIR__.'/auth.php';
