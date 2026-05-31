<?php

use App\Http\Controllers\Web\Auth\ConfirmPasswordController;
use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\Auth\NewPasswordController;
use App\Http\Controllers\Web\Auth\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;

// ── Guest routes ──────────────────────────────────────────────────
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    // Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    // Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    // Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    // Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// ── Authenticated routes ──────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Route::get('/confirm-password', [ConfirmPasswordController::class, 'show'])->name('password.confirm');
    // Route::post('/confirm-password', [ConfirmPasswordController::class, 'store']);
});
