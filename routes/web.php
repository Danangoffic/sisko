<?php

use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::get('/kelas', [SchoolClassController::class, 'index'])->name('class.index');
    Route::post('/kelas', [SchoolClassController::class, 'store'])->name('class.store');
    Route::delete('/kelas/{school_class}', [SchoolClassController::class, 'destroy'])->name('class.destroy');

    Route::middleware('role:admin')->group(function () {
        Route::inertia('/users', 'users/index')->name('users.index');
        Route::resource('teachers', TeacherController::class)->except(['create', 'edit', 'show']);
    });
});

require __DIR__.'/settings.php';
