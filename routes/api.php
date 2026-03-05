<?php

use App\Http\Controllers\Admin\Communication\EmailCommunicationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Inbound
Route::withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->post('/webhooks/email/incoming', [EmailCommunicationController::class, 'incoming']);
