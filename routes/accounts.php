<?php

// use App\Http\Controllers\CourtsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SuperAdminAuthController;
use App\Http\Controllers\Auth\AdminRegisteredController;
use App\Http\Controllers\Auth\AdminAuthController;


use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\AdminController;
use App\Http\Controllers\SuperAdmin\UserController;

use App\Http\Controllers\SuperAdmin\CourtsController;
use App\Http\Controllers\SuperAdmin\SportsController;
use App\Http\Controllers\SuperAdmin\OwnerApplicationController as SuperOwnerApplicationController;
use App\Http\Controllers\SuperAdmin\ProfileController as SuperAdminProfileController;

use App\Http\Controllers\Admin\OwnerApplicationController;
use App\Http\Controllers\admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\admin\MyCourtsController;
use App\Http\Controllers\admin\ReservationsController;
use App\Http\Controllers\admin\MyProfileController;
use App\Http\Controllers\admin\SubscriptionController;
use App\Http\Controllers\admin\GroupGameController;
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

        Route::get('/profile', [SuperAdminProfileController::class, 'edit'])
            ->name('profile.edit');
        Route::patch('/profile', [SuperAdminProfileController::class, 'update'])
            ->name('profile.update');
        Route::put('/profile/password', [SuperAdminProfileController::class, 'updatePassword'])
            ->name('profile.password');

        Route::get('/admins', [AdminController::class, 'index'])
            ->name('admins.index');

        Route::patch('/admins/{admin}/status', [AdminController::class, 'updateStatus'])
            ->name('admins.status');



        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');

        Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])
            ->name('users.status');



        Route::get('/sports', [SportsController::class, 'index'])
            ->name('sports.index');

        Route::post('/sports', [SportsController::class, 'store'])
            ->name('sports.store');

        Route::put('/sports/{sport}', [SportsController::class, 'update'])
            ->name('sports.update');

        Route::delete('/sports/{sport}', [SportsController::class, 'destroy'])
            ->name('sports.destroy');



        Route::get('/courts', [CourtsController::class, 'index'])
            ->name('courts.index');

        Route::get('/courts/{court}', [CourtsController::class, 'show'])
            ->name('courts.show');

        Route::patch('/courts/{court}/status', [CourtsController::class, 'updateStatus'])
            ->name('courts.status');



        // Owner Applications
        Route::get('/applications', [SuperOwnerApplicationController::class, 'index'])
            ->name('applications.index');

        Route::get('/applications/{application}', [SuperOwnerApplicationController::class, 'show'])
            ->name('applications.show');

        Route::patch('/applications/{application}/approve', [SuperOwnerApplicationController::class, 'approve'])
            ->name('applications.approve');

        Route::patch('/applications/{application}/reject', [SuperOwnerApplicationController::class, 'reject'])
            ->name('applications.reject');

        Route::get('/applications/{application}/attachments/{attachment}', [SuperOwnerApplicationController::class, 'download'])
            ->name('applications.attachments.download');

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

        Route::get('/pending', [OwnerApplicationController::class, 'index'])
            ->name('pending');

        Route::view('/terms', 'admins.owner-terms')->name('terms');

        Route::post('/application', [OwnerApplicationController::class, 'store'])
            ->name('application.store');

        Route::post('/logout', [AdminAuthController::class, 'logout'])
            ->name('logout');


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

        // Admin Profile
        Route::get('/profile', [MyProfileController::class, 'index'])
            ->name('profile.index');

        Route::put('/profile', [MyProfileController::class, 'update'])
            ->name('profile.update');

        // Subscriptions Management
        Route::resource('subscriptions', SubscriptionController::class);

        // Group Games Management
        Route::resource('group-games', GroupGameController::class)
            ->except(['show']);

        Route::patch('group-games/{id}/cancel', [GroupGameController::class, 'cancel'])
            ->name('group-games.cancel');

        Route::middleware('admin.active')->group(function () {
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])
                ->name('dashboard');
        });
    });
});
