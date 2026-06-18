<?php

use App\Http\Controllers\WebApi\AdminUserController;
use App\Http\Controllers\WebApi\AuthController;
use App\Http\Controllers\WebApi\CategoryController;
use App\Http\Controllers\WebApi\ProfileController;
use App\Http\Controllers\WebApi\TaskTemplateController;
use App\Http\Controllers\WebApi\TodayController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('web_api.auth.login');
    Route::post('register', [AuthController::class, 'register'])->name('web_api.auth.register');
});

Route::middleware(['auth', 'approved'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('web_api.auth.logout');
    Route::post('auth/confirm-password', [AuthController::class, 'confirmPassword'])->name('web_api.auth.confirm_password');
    Route::post('auth/verification-notification', [AuthController::class, 'sendVerification'])
        ->middleware('throttle:6,1')
        ->name('web_api.auth.verification_send');

    Route::patch('profile', [ProfileController::class, 'update'])->name('web_api.profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('web_api.profile.password');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('web_api.profile.destroy');
});

Route::middleware(['auth', 'approved', 'verified'])->group(function () {
    Route::post('categories', [CategoryController::class, 'store'])->name('web_api.categories.store');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->name('web_api.categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('web_api.categories.destroy');

    Route::post('tasks', [TaskTemplateController::class, 'store'])->name('web_api.tasks.store');
    Route::put('tasks/{task}', [TaskTemplateController::class, 'update'])->name('web_api.tasks.update');
    Route::delete('tasks/{task}', [TaskTemplateController::class, 'destroy'])->name('web_api.tasks.destroy');
    Route::patch('tasks/{task}/toggle', [TaskTemplateController::class, 'toggle'])->name('web_api.tasks.toggle');

    Route::post('today/quick-task', [TodayController::class, 'quickTask'])->name('web_api.today.quick_task');
    Route::patch('today/{instance}/done', [TodayController::class, 'done'])->name('web_api.today.done');
    Route::patch('today/{instance}/skip', [TodayController::class, 'skip'])->name('web_api.today.skip');
    Route::patch('today/{instance}/undo', [TodayController::class, 'undo'])->name('web_api.today.undo');
    Route::post('today/close', [TodayController::class, 'close'])->name('web_api.today.close');

    Route::middleware('admin')->prefix('admin')->name('web_api.admin.')->group(function () {
        Route::patch('users/{user}/approve', [AdminUserController::class, 'approve'])->name('users.approve');
        Route::patch('users/{user}/adjust-hp', [AdminUserController::class, 'adjustHp'])->name('users.adjust_hp');
        Route::put('users/{user}/password', [AdminUserController::class, 'resetPassword'])->name('users.reset_password');
        Route::delete('users/{user}/reject', [AdminUserController::class, 'reject'])->name('users.reject');
    });
});
