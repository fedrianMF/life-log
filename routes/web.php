<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::inertia('/login', 'Auth/Login')->name('login');
    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('home');
    Route::resource('events', EventController::class)->except('show');
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');
    Route::post('/logout', LogoutController::class)->name('logout');
});
