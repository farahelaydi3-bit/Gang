<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SuperAdminAuthController;
use App\Http\Controllers\Auth\AdminRegisteredController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\AdminController;


// =========================
// Super Admin Routes
// =========================

Route::prefix('super-admin')->name('super_admin.')->group(function () {

    Route::middleware('guest:super_admin')->group(function () {

        Route::get('/login', [SuperAdminAuthController::class, 'showLogin'])
            ->name('login');

        Route::post('/login', [SuperAdminAuthController::class, 'login'])
            ->name('login.submit');
    });

    Route::middleware('auth:super_admin')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/admins', [AdminController::class, 'index'])
            ->name('admins.index');

        Route::patch('/admins/{admin}/status', [AdminController::class, 'updateStatus'])
            ->name('admins.status');

        Route::post('/logout', [SuperAdminAuthController::class, 'logout'])
            ->name('logout');
    });

});

// =========================
// Court Owner Routes
// =========================

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest:admin')->group(function () {

        Route::get('/register', [AdminRegisteredController::class, 'create'])
            ->name('register');

        Route::post('/register', [AdminRegisteredController::class, 'store'])
            ->name('register.store');

        Route::get('/login', [AdminAuthController::class, 'showLogin'])
            ->name('login');

        Route::post('/login', [AdminAuthController::class, 'login'])
            ->name('login.submit');
    });

    Route::middleware('auth:admin')->group(function () {

        Route::view('/pending', 'admins.pending')
            ->name('pending');

        Route::post('/logout', [AdminAuthController::class, 'logout'])
            ->name('logout');

        Route::middleware('admin.active')->group(function () {

            Route::view('/dashboard', 'admins.dashboard')
                ->name('dashboard');
        });
    });

});
