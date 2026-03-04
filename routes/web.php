<?php

use App\Http\Controllers\Admin\CreditControllers\CreditSenseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Public Application Routes (No Authentication Required)
Route::get('apply',  [ApplicationController::class, 'create'])->name('applications.create');
Route::post('apply', [ApplicationController::class, 'store'])->name('applications.store');
Route::get('privacy-policy', function () {
    return view('pages.public.privacy-policy');
})->name('privacy-policy');
Route::get('terms-and-conditions', function () {
    return view('pages.public.terms-and-conditions');
})->name('terms-and-conditions');

/*
|--------------------------------------------------------------------------
| Authenticated Client Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->group(base_path('routes/clientRoutes.php'));

// CreditSense (client-facing — authenticated)
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
    ->prefix('creditsense')
    ->name('creditsense.')
    ->group(function () {
        Route::get('{application}/config',    [CreditSenseController::class, 'iframeConfig'])->name('config');
        Route::post('{application}/complete', [CreditSenseController::class, 'complete'])->name('complete');
    });

/*
|--------------------------------------------------------------------------
| Admin/Assessor Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'role:admin|assessor'])
    ->prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin/adminRoutes.php'));

// Twilio Webhooks (no CSRF protection needed)
Route::post('/webhooks/twilio/sms', function (\Illuminate\Http\Request $request) {
    app(\App\Services\TwilioService::class)->handleIncoming($request->all());
    return response('OK', 200);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/webhooks/twilio/status', function (\Illuminate\Http\Request $request) {
    $messageSid = $request->input('MessageSid');
    $status = $request->input('MessageStatus');

    app(\App\Services\TwilioService::class)->updateStatus($messageSid, $status);
    return response('OK', 200);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// CreditSense Webhook (no CSRF — verified by HMAC signature)
Route::post('/webhooks/creditsense', [CreditSenseController::class, 'webhook'])
    ->name('webhooks.creditsense')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
