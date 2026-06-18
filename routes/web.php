<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskTemplateController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::prefix('web_api')->group(function () {
    require __DIR__.'/web_api.php';
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/tasks', [TaskTemplateController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/create', [TaskTemplateController::class, 'create'])->name('tasks.create');
    Route::get('/tasks/{task}/edit', [TaskTemplateController::class, 'edit'])->name('tasks.edit');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

    Route::get('/reports/day/{date}', [ReportController::class, 'day'])->name('reports.day');
    Route::get('/reports/week/{year}/{week}', [ReportController::class, 'week'])->name('reports.week');
    Route::get('/reports/month/{year}/{month}', [ReportController::class, 'month'])->name('reports.month');
    Route::get('/reports/overview', [ReportController::class, 'overview'])->name('reports.overview');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/users/{user}', [AdminController::class, 'userDetail'])->name('users.show');
        Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});

require __DIR__.'/auth.php';
