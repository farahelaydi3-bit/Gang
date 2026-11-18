<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminAuthController;

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])
            ->name('login');

        Route::post('/login', [AdminAuthController::class, 'login'])
            ->name('login.submit');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::view('/dashboard', 'admins.dashboard')
            ->name('dashboard');

        Route::post('/logout', [AdminAuthController::class, 'logout'])
            ->name('logout');
    });
});
