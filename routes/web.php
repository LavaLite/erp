<?php

use App\Http\Controllers\Api\EmailVerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Guest routes (for unauthenticated users)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::get('/reset-password/{token}', function ($token) {
    return view('auth.reset-password', ['token' => $token]);
})->name('password.reset');

// Login submission (web)
Route::post('/login', [\App\Http\Controllers\Web\AuthController::class, 'login'])
    ->name('login.submit');

// Social login routes
Route::get('/auth/{provider}/redirect', [\App\Http\Controllers\Web\SocialAuthController::class, 'redirect'])
    ->name('social.redirect');
Route::get('/auth/{provider}/callback', [\App\Http\Controllers\Web\SocialAuthController::class, 'callback'])
    ->name('social.callback');

// Email verification route (GET for clicking links in emails)
Route::get('/verify-email', [EmailVerificationController::class, 'verifyViaGet'])
    ->name('verification.verify');

// Email not verified page - accessible to authenticated users
Route::get('/email-not-verified', function () {
    return view('auth.email-not-verified');
})->middleware('auth:web')
    ->name('email.not.verified');

// Logout (web)
Route::post('/logout', [\App\Http\Controllers\Web\AuthController::class, 'logout'])
    ->middleware('auth:web')
    ->name('logout');

// Protected web routes
Route::middleware(['auth:web', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/settings', function () {
        return view('settings');
    })->name('settings');

    Route::get('/change-password', function () {
        return view('settings');
    })->name('password.change');
});
