<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\SportsController;
use App\Http\Controllers\CourtsController;
use App\Http\Controllers\GroupGameController;
use App\Http\Controllers\WaitingListController;
use App\Http\Controllers\HomeController;

// CourtBook - Public Pages

Route::get('/', [HomeController::class, 'index'])
    ->name('courtbook.home');

Route::view('/courtbook/profile', 'pages.profile')->name('courtbook.profile');
Route::view('/courtbook/login', 'pages.login')->name('courtbook.login');
Route::view('/courtbook/register', 'pages.register')->name('courtbook.register');
Route::view('/courtbook/forgot-password', 'pages.forgot-password')->name('courtbook.forgot-password');

Route::view('/courts', 'pages.courts')->name('courtbook.courts');
Route::view('/courts/{court}', 'pages.court')->name('courtbook.court');
Route::view('/nearby', 'pages.nearby')->name('courtbook.nearby');

Route::get('/bookings', [BookingController::class, 'index'])
    ->name('courtbook.bookings');

Route::view('/my-games', 'pages.my-games')->name('courtbook.my-games');
Route::view('/notifications', 'pages.notifications')->name('courtbook.notifications');
Route::view('/assistant', 'pages.assistant')->name('courtbook.assistant');
Route::view('/modals', 'pages.modals')->name('courtbook.modals');

// Player Dashboard

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Player Profile - Breeze Authentication

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Subscriptions

Route::get('/subscriptions', [SubscriptionController::class, 'create'])
    ->name('courtbook.subscriptions');

Route::resource('subscriptions', SubscriptionController::class)
    ->except(['index', 'create']);

// Group Games

Route::get('/games', [GroupGameController::class, 'index'])->name('games.index');
Route::get('/games/create', [GroupGameController::class, 'create'])->name('games.create');
Route::post('/games', [GroupGameController::class, 'store'])->name('games.store');
Route::get('/games/{game}', [GroupGameController::class, 'show'])->name('games.show');
Route::get('/games/{game}/edit', [GroupGameController::class, 'edit'])->name('games.edit');
Route::put('/games/{game}', [GroupGameController::class, 'update'])->name('games.update');
Route::delete('/games/{game}', [GroupGameController::class, 'destroy'])->name('games.destroy');

Route::post('/games/{game}/join', [GroupGameController::class, 'join'])->name('games.join');
Route::post('/games/{game}/leave', [GroupGameController::class, 'leave'])->name('games.leave');

// Waiting List

Route::get('/waitlist', [WaitingListController::class, 'index'])->name('waitlist.index');
Route::get('/waitlist/create', [WaitingListController::class, 'create'])->name('waitlist.create');
Route::post('/waitlist', [WaitingListController::class, 'store'])->name('waitlist.store');
Route::delete('/waitlist/{id}', [WaitingListController::class, 'destroy'])->name('waitlist.destroy');

// Reviews Management

Route::get('/reviews', [ReviewController::class, 'index'])
    ->name('reviews.index');

Route::middleware('auth')->group(function () {
    Route::resource('reviews', ReviewController::class)
        ->except(['index', 'show']);
});

Route::get('/reviews/{review}', [ReviewController::class, 'show'])
    ->whereNumber('review')
    ->name('reviews.show');

require __DIR__ . '/auth.php';