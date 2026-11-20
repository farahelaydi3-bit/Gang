<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\SportsController;
use App\Http\Controllers\CourtsController;
use App\Http\Controllers\HomeController;


Route::view('/courtbook/profile', 'pages.profile')->name('courtbook.profile');

Route::view('/courtbook/login', 'pages.login')->name('courtbook.login');
Route::view('/courtbook/register', 'pages.register')->name('courtbook.register');
Route::view('/courtbook/forgot-password', 'pages.forgot-password')->name('courtbook.forgot-password');

// ==========================================
// CourtBook - Public Pages
// ==========================================

Route::get('/', [HomeController::class, 'index'])
    ->name('courtbook.home');

Route::view('/courtbook/profile', 'pages.profile')->name('courtbook.profile');

Route::view('/courtbook/login', 'pages.login')->name('courtbook.login');
Route::view('/courtbook/register', 'pages.register')->name('courtbook.register');
Route::view('/courtbook/forgot-password', 'pages.forgot-password')->name('courtbook.forgot-password');

Route::view('/courts', 'pages.courts')->name('courtbook.courts');
Route::view('/courts/{court}', 'pages.court')->name('courtbook.court');
Route::view('/nearby', 'pages.nearby')->name('courtbook.nearby');

Route::view('/games', 'pages.games')->name('courtbook.games');
Route::view('/games/create', 'pages.create-game')->name('courtbook.create-game');
Route::view('/games/{game}', 'pages.game')->name('courtbook.game');

Route::view('/waitlist', 'pages.waitlist')->name('courtbook.waitlist');



Route::get('/bookings', [BookingController::class, 'index'])->name('courtbook.bookings');
Route::view('/my-games', 'pages.my-games')->name('courtbook.my-games');
Route::view('/courtbook/profile', 'pages.profile')->name('courtbook.profile');

Route::view('/courtbook/login', 'pages.login')->name('courtbook.login');
Route::view('/courtbook/register', 'pages.register')->name('courtbook.register');
Route::view('/courtbook/forgot-password', 'pages.forgot-password')->name('courtbook.forgot-password');

Route::view('/notifications', 'pages.notifications')->name('courtbook.notifications');

Route::view('/assistant', 'pages.assistant')->name('courtbook.assistant');
Route::view('/modals', 'pages.modals')->name('courtbook.modals');

// ==========================================
// Player Dashboard
// ==========================================

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ==========================================
// Player Profile - Breeze Authentication
// ==========================================

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// Route::get('/subscriptions', [SubscriptionController::class, 'create'])
//     ->name('courtbook.subscriptions');

// // Authenticated subscription management
// Route::middleware('auth')->group(function () {

//     Route::get('/my-subscriptions', [SubscriptionController::class, 'index'])
//         ->name('courtbook.my-subscriptions');

//     Route::resource('subscriptions', SubscriptionController::class)
//         ->except(['index', 'create']);
// });

require __DIR__ . '/auth.php';


