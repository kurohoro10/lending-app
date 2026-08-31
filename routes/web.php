<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\DirectorExpenseController;
use App\Http\Controllers\Auth\EmailTwoFactorChallengeController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('welcome');

// Resend the email OTP code while a login is mid two-factor challenge.
Route::post('two-factor-challenge/resend-email-code', [EmailTwoFactorChallengeController::class, 'resend'])
    ->middleware(['web', 'throttle:email-otp-resend'])
    ->name('two-factor.email.resend');

Route::get('apply',  [ApplicationController::class, 'create'])->name('applications.create');
Route::post('apply', [ApplicationController::class, 'store'])->name('applications.store');
Route::get('privacy-policy',       fn () => view('pages.public.privacy-policy'))->name('privacy-policy');
Route::get('terms-and-conditions', fn () => view('pages.public.terms-and-conditions'))->name('terms-and-conditions');

// Authenticated client routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->group(base_path('routes/clientRoutes.php'));

// Admin routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'role:admin|assessor'])
    ->prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin/adminRoutes.php'));

Route::get('/director-expenses/{application}/{director}',
    [DirectorExpenseController::class, 'show'])
    ->name('director.expenses.show')
    ->middleware('signed');

Route::post('/director-expenses/{application}/{director}',
    [DirectorExpenseController::class, 'store'])
    ->name('director.expenses.store');
