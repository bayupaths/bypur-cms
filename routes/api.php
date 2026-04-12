<?php

use App\Http\Controllers\Api\Auth\PermissionController;
use App\Http\Controllers\Api\Auth\RoleController;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\Menu\MenuController;
use App\Http\Controllers\Api\Menu\MenuGroupController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

// ── Notifications (no version prefix — matches /api/notifications) ─────────
Route::middleware(['web', 'auth'])->prefix('notifications')->name('api.notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::patch('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
});

Route::prefix('v1')->name('api.v1.')->middleware(['auth:web'])->group(function () {
    // ── Auth Management ───────────────────────────────────────────
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->name('roles.permissions.sync');
        Route::apiResource('permissions', PermissionController::class);
        Route::post('permissions/{permission}/conditions', [PermissionController::class, 'syncConditions'])->name('permissions.conditions.sync');
    });

    // ── Menu Management ───────────────────────────────────────────
    Route::prefix('menu')->name('menu.')->group(function () {
        Route::apiResource('groups', MenuGroupController::class);
        Route::post('items/reorder', [MenuController::class, 'reorder'])->name('items.reorder');
        Route::get('items/tree/{group}', [MenuController::class, 'tree'])->name('items.tree');
        Route::apiResource('items', MenuController::class);
    });
});
