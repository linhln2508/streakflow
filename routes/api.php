<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TaskTemplateController;
use App\Http\Controllers\Api\TodayController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->name('api.login');

Route::middleware(['auth:sanctum', 'approved'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('me', [AuthController::class, 'me'])->name('api.me');

    Route::post('categories', [CategoryController::class, 'store'])->name('api.categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('api.categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('api.categories.destroy');

    Route::post('tasks', [TaskTemplateController::class, 'store'])->name('api.tasks.store');
    Route::put('tasks/{task}', [TaskTemplateController::class, 'update'])->name('api.tasks.update');
    Route::delete('tasks/{task}', [TaskTemplateController::class, 'destroy'])->name('api.tasks.destroy');
    Route::patch('tasks/{task}/toggle', [TaskTemplateController::class, 'toggle'])->name('api.tasks.toggle');

    Route::post('today/quick-task', [TodayController::class, 'quickTask'])->name('api.today.quick_task');
    Route::patch('today/{instance}/done', [TodayController::class, 'done'])->name('api.today.done');
    Route::patch('today/{instance}/skip', [TodayController::class, 'skip'])->name('api.today.skip');
    Route::patch('today/{instance}/undo', [TodayController::class, 'undo'])->name('api.today.undo');
    Route::post('today/close', [TodayController::class, 'close'])->name('api.today.close');
});
