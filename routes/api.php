<?php
// routes/api.php
use App\Http\Controllers\Admin\Communication\EmailCommunicationController;
use App\Http\Controllers\Admin\Communication\SmsCommunicationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CreditControllers\CreditSenseController;
use App\Http\Controllers\Admin\CreditControllers\BasiqController;
use App\Http\Middleware\VerifyCsrfToken;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Inbound
Route::withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->post('/webhooks/email/incoming', [EmailCommunicationController::class, 'incoming']);

// routes/api.php
Route::prefix('webhooks')->group(function () {
    Route::post('twilio/sms',    [SmsCommunicationController::class, 'incoming'])
        ->name('webhooks.sms.incoming');
    Route::post('twilio/status', [SmsCommunicationController::class, 'deliveryStatus'])
        ->name('webhooks.sms.status');

    Route::post('creditsense', [CreditSenseController::class, 'webhook'])
        ->name('webhooks.creditsense');
    
    // NEW: Basiq webhook
    Route::post('basiq', [BasiqController::class, 'webhook'])
        ->withoutMiddleware([VerifyCsrfToken::class])
        ->name('webhooks.basiq');
});
