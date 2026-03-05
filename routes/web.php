<?php

use App\Http\Controllers\Admin\Communication\SmsCommunicationController;
use App\Http\Controllers\ApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('welcome');

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

// Webhooks — no auth, no CSRF
Route::withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->prefix('webhooks')
    ->group(function () {

        // Twilio SMS
        Route::post('twilio/sms',    [SmsCommunicationController::class, 'incoming'])
            ->name('webhooks.sms.incoming');
        Route::post('twilio/status', [SmsCommunicationController::class, 'deliveryStatus'])
            ->name('webhooks.sms.status');
    });
