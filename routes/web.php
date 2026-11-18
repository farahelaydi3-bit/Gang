<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('courtbook.home');
Route::view('/courts', 'pages.courts')->name('courtbook.courts');
Route::view('/courts/{court}', 'pages.court')->name('courtbook.court');
Route::view('/nearby', 'pages.nearby')->name('courtbook.nearby');
Route::view('/games', 'pages.games')->name('courtbook.games');
Route::view('/games/create', 'pages.create-game')->name('courtbook.create-game');
Route::view('/games/{game}', 'pages.game')->name('courtbook.game');
Route::view('/waitlist', 'pages.waitlist')->name('courtbook.waitlist');
Route::view('/subscriptions', 'pages.subscriptions')->name('courtbook.subscriptions');
Route::view('/my-subscriptions', 'pages.my-subscriptions')->name('courtbook.my-subscriptions');
Route::view('/bookings', 'pages.bookings')->name('courtbook.bookings');
Route::view('/my-games', 'pages.my-games')->name('courtbook.my-games');
Route::view('/profile', 'pages.profile')->name('courtbook.profile');
Route::view('/notifications', 'pages.notifications')->name('courtbook.notifications');
Route::view('/login', 'pages.login')->name('courtbook.login');
Route::view('/register', 'pages.register')->name('courtbook.register');
Route::view('/forgot-password', 'pages.forgot-password')->name('courtbook.forgot-password');
Route::view('/assistant', 'pages.assistant')->name('courtbook.assistant');
Route::view('/modals', 'pages.modals')->name('courtbook.modals');
