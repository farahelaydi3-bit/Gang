<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SuperAdminAuthController;
use App\Http\Controllers\Auth\AdminRegisteredController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\SuperAdmin\CourtsController;
use App\Http\Controllers\SuperAdmin\SportsController;
use App\Http\Controllers\SuperAdmin\OwnerApplicationController as SuperOwnerApplicationController;

use App\Http\Controllers\Admin\OwnerApplicationController;
use App\Http\Controllers\admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\admin\MyCourtsController;
use App\Http\Controllers\admin\ReservationsController;
use App\Http\Controllers\Admin\GangSiteStudioController;
use App\Http\Controllers\SuperAdmin\GangSiteManagementController;

// =========================
// Super Admin Routes
// =========================
Route::prefix('super-admin')->name('super_admin.')->group(function () {

    Route::middleware('guest:super_admin')->group(function () {
        Route::get('/login', [SuperAdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [SuperAdminAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth:super_admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/admins', [AdminController::class, 'index'])->name('admins.index');
        Route::patch('/admins/{admin}/status', [AdminController::class, 'updateStatus'])->name('admins.status');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])->name('users.status');

        Route::get('/sports', [SportsController::class, 'index'])->name('sports.index');
        Route::post('/sports', [SportsController::class, 'store'])->name('sports.store');
        Route::put('/sports/{sport}', [SportsController::class, 'update'])->name('sports.update');
        Route::delete('/sports/{sport}', [SportsController::class, 'destroy'])->name('sports.destroy');

        Route::get('/courts', [CourtsController::class, 'index'])->name('courts.index');
        Route::get('/courts/{court}', [CourtsController::class, 'show'])->name('courts.show');
        Route::patch('/courts/{court}/status', [CourtsController::class, 'updateStatus'])->name('courts.status');

        // Owner applications
        Route::get('/applications', [SuperOwnerApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/{application}', [SuperOwnerApplicationController::class, 'show'])->name('applications.show');
        Route::patch('/applications/{application}/approve', [SuperOwnerApplicationController::class, 'approve'])->name('applications.approve');
        Route::patch('/applications/{application}/reject', [SuperOwnerApplicationController::class, 'reject'])->name('applications.reject');
        Route::get('/applications/{application}/attachments/{attachment}', [SuperOwnerApplicationController::class, 'download'])
            ->name('applications.attachments.download');

        // Gang Site Studio - Super Admin only
        Route::get('/site-studio', [GangSiteManagementController::class, 'index'])->name('site-studio.index');
        Route::patch('/site-studio/{website}/toggle', [GangSiteManagementController::class, 'toggle'])->name('site-studio.toggle');
        Route::patch('/site-studio/{website}/renew', [GangSiteManagementController::class, 'renew'])->name('site-studio.renew');

        Route::post('/logout', [SuperAdminAuthController::class, 'logout'])->name('logout');
    });
});

// =========================
// Court Owner (Admin) Routes
// =========================
Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest:admin')->group(function () {
        Route::get('/register', [AdminRegisteredController::class, 'create'])->name('register');
        Route::post('/register', [AdminRegisteredController::class, 'store'])->name('register.store');
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth:admin')->group(function () {
        // Available to logged-in owners, including pending owners
        Route::get('/pending', [OwnerApplicationController::class, 'index'])->name('pending');
        Route::post('/application', [OwnerApplicationController::class, 'store'])->name('application.store');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        // Only active owners may access dashboard, courts and reservations
        Route::middleware('admin.active')->group(function () {
            // Owner Subscriptions
Route::get('/subscription', [AdminSubscriptionController::class, 'index'])
    ->name('subscription.index');
            // Dashboard
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            // My Courts
            Route::get('/courts', [MyCourtsController::class, 'index'])->name('courts.index');
            Route::get('/courts/create', [MyCourtsController::class, 'create'])->name('courts.create');
            Route::post('/courts/store', [MyCourtsController::class, 'store'])->name('courts.store');
            Route::get('/courts/{id}/edit', [MyCourtsController::class, 'edit'])->name('courts.edit');
            Route::put('/courts/{id}', [MyCourtsController::class, 'update'])->name('courts.update');
            Route::delete('/courts/{id}', [MyCourtsController::class, 'destroy'])->name('courts.destroy');

            // Reservations
            Route::get('/reservations', [ReservationsController::class, 'index'])->name('reservations.index');
            Route::patch('/reservations/{id}/confirm', [ReservationsController::class, 'confirm'])->name('reservations.confirm');
            Route::patch('/reservations/{id}/cancel', [ReservationsController::class, 'cancel'])->name('reservations.cancel');
            Route::patch('/reservations/{id}/complete', [ReservationsController::class, 'complete'])->name('reservations.complete');

            // Gang Site Studio - active owners only
            Route::get('/site-studio', [GangSiteStudioController::class, 'index'])->name('site-studio.index');
            Route::post('/site-studio', [GangSiteStudioController::class, 'store'])->name('site-studio.store');
            Route::put('/site-studio', [GangSiteStudioController::class, 'update'])->name('site-studio.update');
            Route::patch('/site-studio/sections/{section}', [GangSiteStudioController::class, 'updateSection'])->name('site-studio.section');
            Route::delete('/site-studio/sections/{section}/image', [GangSiteStudioController::class, 'deleteImage'])->name('site-studio.image.delete');
            Route::post('/site-studio/generate', [GangSiteStudioController::class, 'generate'])->middleware('throttle:3,1')->name('site-studio.generate');
            Route::get('/site-studio/preview', [GangSiteStudioController::class, 'preview'])->name('site-studio.preview');
            Route::post('/site-studio/publish', [GangSiteStudioController::class, 'publish'])->name('site-studio.publish');
            Route::post('/site-studio/unpublish', [GangSiteStudioController::class, 'unpublish'])->name('site-studio.unpublish');
        });
    });
});
