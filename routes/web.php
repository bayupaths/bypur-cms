<?php

use App\Http\Controllers\Web\Auth\PermissionController;
use App\Http\Controllers\Web\Auth\RoleController;
use App\Http\Controllers\Web\Auth\UserController;
use App\Http\Controllers\Web\Menu\MenuController;
use App\Http\Controllers\Web\Menu\MenuGroupController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Authenticated routes ──────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // ── Auth Management ───────────────────────────────────────────
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
    });

    // ── Menu Management ───────────────────────────────────────────
    Route::prefix('menu')->name('menu.')->group(function () {
        // Menu Groups
        Route::prefix('groups')->name('groups.')->group(function () {
            Route::get('/', [MenuGroupController::class, 'index'])->name('index');
            Route::get('/create', [MenuGroupController::class, 'create'])->name('create');
            Route::post('/', [MenuGroupController::class, 'store'])->name('store');
            Route::get('/{menuGroup}/edit', [MenuGroupController::class, 'edit'])->name('edit');
            Route::put('/{menuGroup}', [MenuGroupController::class, 'update'])->name('update');
            Route::delete('/{menuGroup}', [MenuGroupController::class, 'destroy'])->name('destroy');
        });

        // Menu Items
        Route::get('/', [MenuController::class, 'index'])->name('index');
        Route::get('/create', [MenuController::class, 'create'])->name('create');
        Route::post('/', [MenuController::class, 'store'])->name('store');
        Route::post('/reorder', [MenuController::class, 'reorder'])->name('reorder');
        Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('edit');
        Route::put('/{menu}', [MenuController::class, 'update'])->name('update');
        Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');
    });
});
