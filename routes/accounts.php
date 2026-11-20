<?php

use App\Http\Controllers\CourtsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SuperAdminAuthController;
use App\Http\Controllers\Auth\AdminRegisteredController;
use App\Http\Controllers\Auth\AdminAuthController;


use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\UserController;

use App\Http\Controllers\admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\admin\MyCourtsController;
use App\Http\Controllers\admin\ReservationsController;
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



        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');

        Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])
            ->name('users.status');


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

        Route::view('/pending', 'admins.dashboard.pending')
            ->name('pending');

        Route::post('/logout', [AdminAuthController::class, 'logout'])
            ->name('logout');

        Route::middleware('admin.active')->group(function () {

            // Dashboard
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])
                ->name('dashboard');
            // My Courts
            Route::get('/courts', [MyCourtsController::class, 'index'])
                ->name('courts.index');

            Route::get('/courts/create', [MyCourtsController::class, 'create'])
                ->name('courts.create');

            Route::post('/courts/store', [MyCourtsController::class, 'store'])
                ->name('courts.store');

            Route::get('/courts/{id}/edit', [MyCourtsController::class, 'edit'])
                ->name('courts.edit');

            Route::put('/courts/{id}', [MyCourtsController::class, 'update'])
                ->name('courts.update');

            Route::delete('/courts/{id}', [MyCourtsController::class, 'destroy'])
                ->name('courts.destroy');

            // Reservations
            Route::get('/reservations', [ReservationsController::class, 'index'])
                ->name('reservations.index');

            Route::patch('/reservations/{id}/confirm', [ReservationsController::class, 'confirm'])
                ->name('reservations.confirm');

            Route::patch('/reservations/{id}/cancel', [ReservationsController::class, 'cancel'])
                ->name('reservations.cancel');

            Route::patch('/reservations/{id}/complete', [ReservationsController::class, 'complete'])
                ->name('reservations.complete');
        });
    });
});
