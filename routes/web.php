<?php

use App\Http\Controllers\Web\Auth\PermissionController;
use App\Http\Controllers\Web\Auth\RoleController;
use App\Http\Controllers\Web\Auth\UserController;
use App\Http\Controllers\Web\Menu\MenuController;
use App\Http\Controllers\Web\Menu\MenuGroupController;
use App\Http\Controllers\Web\Profile\CertificateController;
use App\Http\Controllers\Web\Profile\EducationController;
use App\Http\Controllers\Web\Profile\ExperienceController;
use App\Http\Controllers\Web\Profile\ServiceController;
use App\Http\Controllers\Web\Profile\SkillController;
use App\Http\Controllers\Web\Profile\ProfileController;
use App\Http\Controllers\Web\Settings\SettingsController;
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
        Route::get('users/{user}/profile', [ProfileController::class, 'showUser'])->name('users.profile');
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
    });

    // ── Profile & CV ─────────────────────────────────────────────
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::match(['put', 'post'], '/', [ProfileController::class, 'update'])->name('update');

        Route::prefix('experiences')->name('experiences.')->group(function () {
            Route::get('/', [ExperienceController::class, 'index'])->name('index');
            Route::post('/', [ExperienceController::class, 'store'])->name('store');
            Route::post('/reorder', [ExperienceController::class, 'reorder'])->name('reorder');
            Route::put('/{experience}', [ExperienceController::class, 'update'])->name('update');
            Route::delete('/{experience}', [ExperienceController::class, 'destroy'])->name('destroy');
        });

        // Educations
        Route::prefix('educations')->name('educations.')->group(function () {
            Route::get('/', [EducationController::class, 'index'])->name('index');
            Route::post('/', [EducationController::class, 'store'])->name('store');
            Route::post('/reorder', [EducationController::class, 'reorder'])->name('reorder');
            Route::put('/{education}', [EducationController::class, 'update'])->name('update');
            Route::delete('/{education}', [EducationController::class, 'destroy'])->name('destroy');
        });

        // Skills
        Route::prefix('skills')->name('skills.')->group(function () {
            Route::get('/', [SkillController::class, 'index'])->name('index');
            Route::post('/', [SkillController::class, 'store'])->name('store');
            Route::post('/reorder', [SkillController::class, 'reorder'])->name('reorder');
            Route::put('/{skill}', [SkillController::class, 'update'])->name('update');
            Route::delete('/{skill}', [SkillController::class, 'destroy'])->name('destroy');
        });

        // Certificates
        Route::prefix('certificates')->name('certificates.')->group(function () {
            Route::get('/', [CertificateController::class, 'index'])->name('index');
            Route::post('/', [CertificateController::class, 'store'])->name('store');
            Route::post('/reorder', [CertificateController::class, 'reorder'])->name('reorder');
            Route::put('/{certificate}', [CertificateController::class, 'update'])->name('update');
            Route::delete('/{certificate}', [CertificateController::class, 'destroy'])->name('destroy');
        });

        // Services
        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/', [ServiceController::class, 'index'])->name('index');
            Route::post('/', [ServiceController::class, 'store'])->name('store');
            Route::post('/reorder', [ServiceController::class, 'reorder'])->name('reorder');
            Route::patch('/{service}/toggle', [ServiceController::class, 'toggle'])->name('toggle');
            Route::put('/{service}', [ServiceController::class, 'update'])->name('update');
            Route::delete('/{service}', [ServiceController::class, 'destroy'])->name('destroy');
        });
    });

    // ── Settings ──────────────────────────────────────────────────
    // Route::prefix('settings')->name('settings.')->group(function () {
    //     Route::get('/account', [SettingsController::class, 'account'])->name('account');
    //     Route::put('/account', [SettingsController::class, 'updateAccount'])->name('account.update');
    // });

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
