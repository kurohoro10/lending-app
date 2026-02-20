<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Sms\WhatsAppController as WhatsAppController;

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

/*
|--------------------------------------------------------------------------
| Admin/Assessor Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'role:admin|assessor'])
    ->prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin/adminRoutes.php'));


// WhatsApp & SMS Routes
Route::post('/whatsapp/send',   [WhatsAppController::class, 'send'])->name('whatsapp.send');
Route::post('/whatsapp/queue',  [WhatsAppController::class, 'queue'])->name('whatsapp.queue');
Route::post('/sms/send',        [WhatsAppController::class, 'sendSMS'])->name('sms.send');
Route::post('/sms/queue',       [WhatsAppController::class, 'queueSMS'])->name('sms.queue');

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

// TEST ROUTES - Remove in production
// Route::middleware(['auth'])->group(function () {
//     Route::get('/test-sms', function () {
//         return view('test-sms');
//     });

//     Route::post('/test-sms-send', function (\Illuminate\Http\Request $request) {
//         $validated = $request->validate([
//             'phone' => 'required|string',
//             'message' => 'required|string',
//             'type' => 'required|in:sms,whatsapp',
//         ]);

//         try {
//             $twilio = app(\App\Services\TwilioService::class);

//             if ($validated['type'] === 'whatsapp') {
//                 $result = $twilio->sendWhatsApp($validated['phone'], $validated['message']);
//             } else {
//                 $result = $twilio->sendSMS($validated['phone'], $validated['message']);
//             }

//             return back()->with('success', 'Message sent! SID: ' . ($result['message_sid'] ?? 'N/A'));
//         } catch (\Exception $e) {
//             return back()->with('error', 'Failed: ' . $e->getMessage());
//         }
//     });

//     // ADD THIS NEW ROUTE
//     Route::get('/test-submit-sms/{application}', function(\App\Models\Application $application) {
//         $application->load('personalDetails', 'user');

//         \Log::info('Test submit SMS triggered', [
//             'application_id' => $application->id,
//             'has_personal_details' => $application->personalDetails !== null,
//             'phone' => $application->personalDetails?->mobile_phone,
//         ]);

//         $service = app(\App\Services\Application\ApplicationNotificationService::class);
//         $service->handleSubmitted($application);

//         return response()->json([
//             'success' => true,
//             'message' => 'SMS sent! Check logs and your WhatsApp.',
//             'application_id' => $application->id,
//             'phone' => $application->personalDetails?->mobile_phone,
//             'log_file' => storage_path('logs/laravel.log')
//         ]);
//     });
// });

// Route::get('/debug-submit/{application}', function(\App\Models\Application $application) {
//     $checks = [
//         'id' => $application->id,
//         'status' => $application->status,
//         'personal_details' => $application->personalDetails !== null,
//         'personal_details_data' => $application->personalDetails,
//         'residential_count' => $application->residentialAddresses()->count(),
//         'residential_data' => $application->residentialAddresses,
//         'employment_count' => $application->employmentDetails()->count(),
//         'employment_data' => $application->employmentDetails,
//         'living_expenses_count' => $application->livingExpenses()->count(),
//         'living_expenses_data' => $application->livingExpenses,
//         'declarations_count' => $application->declarations()->count(),
//         'final_signature' => $application->declarations()
//             ->where('declaration_type', 'final_submission')
//             ->first(),
//         'has_final_signature' => $application->hasFinalSignature(),
//         'can_be_submitted' => $application->canBeSubmitted(),
//     ];

//     return response()->json($checks, 200, [], JSON_PRETTY_PRINT);
// })->middleware('auth');
