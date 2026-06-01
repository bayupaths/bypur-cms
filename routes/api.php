<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Profile\CertificateController;
use App\Http\Controllers\Api\Profile\EducationController;
use App\Http\Controllers\Api\Profile\ExperienceController;
use App\Http\Controllers\Api\Profile\ProfileController;
use App\Http\Controllers\Api\Profile\ServiceController;
use App\Http\Controllers\Api\Profile\SkillController;
use Illuminate\Support\Facades\Route;

// ── Notifications (no version prefix — matches /api/notifications) ─────────
Route::middleware(['web', 'auth'])->prefix('notifications')->name('api.notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::patch('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
});

// ── Profile Public API (X-Api-Key) ─────────────────────────────
Route::prefix('proile')->name('api.v1.proile.')->middleware(['api.key'])->group(function () {
    Route::get('me', [ProfileController::class, 'show'])->name('me');
    Route::get('experiences', [ExperienceController::class, 'index'])->name('experiences');
    Route::get('educations', [EducationController::class, 'index'])->name('educations');
    Route::get('certificates', [CertificateController::class, 'index'])->name('certificates');
    Route::get('skills', [SkillController::class, 'index'])->name('skills');
    Route::get('services', [ServiceController::class, 'index'])->name('services');
});
