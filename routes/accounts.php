<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SuperAdminAuthController;
use App\Http\Controllers\Auth\AdminRegisteredController;
use App\Http\Controllers\Auth\AdminAuthController;



Route::prefix('super-admin')->name('super_admin.')->group(function () {

    Route::middleware('guest:super_admin')->group(function () {
        Route::get('/login', [SuperAdminAuthController::class, 'showLogin'])
            ->name('login');

        Route::post('/login', [SuperAdminAuthController::class, 'login'])
            ->name('login.submit');
    });

    Route::middleware('auth:super_admin')->group(function () {
        Route::view('/dashboard', 'super-admins.dashboard')
            ->name('dashboard');

        Route::post('/logout', [SuperAdminAuthController::class, 'logout'])
            ->name('logout');
    });
});

Route::prefix('admin')->name('admin.')->group(function () {

    // Routes for guests only
    Route::middleware('guest:admin')->group(function () {

        // Registration
        Route::get('/register', [AdminRegisteredController::class, 'create'])
            ->name('register');

        Route::post('/register', [AdminRegisteredController::class, 'store'])
            ->name('register.store');

        // Login
        Route::get('/login', [AdminAuthController::class, 'showLogin'])
            ->name('login');

        Route::post('/login', [AdminAuthController::class, 'login'])
            ->name('login.submit');
    });

    Route::middleware('auth:admin')->group(function () {

        // Available to all authenticated admins
        Route::view('/pending', 'admins.pending')
            ->name('pending');

        Route::post('/logout', [AdminAuthController::class, 'logout'])
            ->name('logout');

        // Available only to active admins
        Route::middleware('admin.active')->group(function () {

            Route::view('/dashboard', 'admins.dashboard')
                ->name('dashboard');
        });
    });
});
