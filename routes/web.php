
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\BookingController;
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

// Route::view('/games/create', 'pages.create-game')->name('courtbook.create-game');
// Route::view('/games/{game}', 'pages.game')->name('courtbook.game');

// Route::view('/waitlist', 'pages.waitlist')->name('courtbook.waitlist');



Route::get('/bookings', [BookingController::class, 'index'])->name('courtbook.bookings');
Route::get('/courts/{court}/availability', [BookingController::class, 'availability'])
    ->name('courtbook.courts.availability');
Route::middleware('auth')->group(function () {
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('courtbook.booking.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('courtbook.bookings.store');
});
Route::view('/my-games', 'pages.my-games')->name('courtbook.my-games');
Route::view('/notifications', 'pages.notifications')->name('courtbook.notifications');
Route::view('/assistant', 'pages.assistant')->name('courtbook.assistant');
Route::view('/modals', 'pages.modals')->name('courtbook.modals');

// Player Dashboard

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Player Profile

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Subscriptions

Route::get('/subscriptions', [SubscriptionController::class, 'create'])
    ->name('courtbook.subscriptions');

Route::middleware('auth')->group(function () {
    Route::get('/my-subscriptions', [SubscriptionController::class, 'index'])
        ->name('courtbook.my-subscriptions');

    Route::resource('subscriptions', SubscriptionController::class)
        ->except(['index', 'create']);
});

// Group Games


// ==========================================
// Group Games
// ==========================================

// List all games
Route::get('/games', [GroupGameController::class, 'index'])
	->name('games.index');

// Keep old links working
Route::get('/courtbook/games', [GroupGameController::class, 'index'])
	->name('courtbook.games');

// Create game
Route::get('/games/create', [GroupGameController::class, 'create'])
	->name('games.create');

Route::post('/games', [GroupGameController::class, 'store'])
    ->middleware('auth')->name('games.store');

// Show game
Route::get('/games/{game}', [GroupGameController::class, 'show'])
	->name('games.show');

// Compatibility with old Blade links
Route::get('/courtbook/games/{game}', [GroupGameController::class, 'show'])
	->name('courtbook.game');

// Edit game
Route::get('/games/{game}/edit', [GroupGameController::class, 'edit'])
	->name('games.edit');

Route::put('/games/{game}', [GroupGameController::class, 'update'])
    ->middleware('auth')->name('games.update');

// Delete game
Route::delete('/games/{game}', [GroupGameController::class, 'destroy'])
    ->middleware('auth')->name('games.destroy');

// Join / Leave
Route::post('/games/{game}/join', [GroupGameController::class, 'join'])
    ->middleware('auth')->name('games.join');

Route::post('/games/{game}/leave', [GroupGameController::class, 'leave'])
    ->middleware('auth')->name('games.leave');


Route::get('/waitlist', [WaitingListController::class, 'index'])->name('waitlist.index');
Route::get('/waitlist/create', [WaitingListController::class, 'create'])->name('waitlist.create');

Route::post('/waitlist', [WaitingListController::class, 'store'])
    ->middleware('auth')->name('waitlist.store');

Route::delete('/waitlist/{id}', [WaitingListController::class, 'destroy'])
    ->middleware('auth')->name('waitlist.destroy');

Route::get('/courtbook/waitlist', [WaitingListController::class, 'index'])
    ->name('courtbook.waitlist');

Route::post('/waiting-list/{waitingList}/accept', [WaitingListController::class, 'accept'])
    ->middleware('auth')->name('waiting-list.accept');

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

Route::middleware('auth')->group(function () {
    Route::get('/bookings/{booking}', [
        BookingController::class,
        'show',
    ])->name('bookings.show');

    Route::put('/bookings/{booking}', [
        BookingController::class,
        'update',
    ])->name('bookings.update');

    Route::post('/bookings/{booking}/cancel', [
        BookingController::class,
        'cancel',
    ])->name('bookings.cancel');
});
require __DIR__ . '/auth.php';
