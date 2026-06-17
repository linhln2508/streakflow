<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TaskTemplateController;
use App\Http\Controllers\TodayController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/tasks', [TaskTemplateController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/create', [TaskTemplateController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [TaskTemplateController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}/edit', [TaskTemplateController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskTemplateController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskTemplateController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('/tasks/{task}/toggle', [TaskTemplateController::class, 'toggle'])->name('tasks.toggle');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::patch('/today/{instance}/done', [TodayController::class, 'done'])->name('today.done');
    Route::patch('/today/{instance}/skip', [TodayController::class, 'skip'])->name('today.skip');
    Route::patch('/today/{instance}/undo', [TodayController::class, 'undo'])->name('today.undo');
    Route::post('/today/close', [TodayController::class, 'close'])->name('today.close');

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
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
