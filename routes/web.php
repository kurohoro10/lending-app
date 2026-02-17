<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PersonalDetailsController;
use App\Http\Controllers\ResidentialAddressController;
use App\Http\Controllers\EmploymentDetailsController;
use App\Http\Controllers\LivingExpenseController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Question\QuestionController as QuestionController;
use App\Http\Controllers\DeclarationController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\CommunicationController;
use App\Http\Controllers\Admin\CreditCheckController;
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
Route::get('apply', [ApplicationController::class, 'create'])->name('applications.create');
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
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    // Dashboard - Redirect based on role
    Route::get('/dashboard', function () {
        if (auth()->user()->canAccessAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('applications.index');
    })->name('dashboard');

    // Client Applications
    Route::get('applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::get('applications/{application}/edit', [ApplicationController::class, 'edit'])->name('applications.edit');
    Route::patch('applications/{application}', [ApplicationController::class, 'update'])->name('applications.update');
    Route::delete('applications/{application}', [ApplicationController::class, 'destroy'])->name('applications.destroy');
    Route::post('applications/{application}/submit', [ApplicationController::class, 'submit'])
        ->name('applications.submit');

    // Personal Details
    Route::post('applications/{application}/personal-details', [PersonalDetailsController::class, 'store'])
        ->name('applications.personal-details.store');

    // Residential Addresses
    Route::get('/api/suburbs/{state}', function ($state) {
        return response()->json(\App\Helpers\AustralianSuburbs::getSuburbsByState($state));
    })->name('api.suburbs');
    Route::post('applications/{application}/residential-addresses', [ResidentialAddressController::class, 'store'])
        ->name('applications.residential-addresses.store');
    Route::patch('applications/{application}/residential-addresses/{residentialAddress}', [ResidentialAddressController::class, 'update'])
        ->name('applications.residential-addresses.update');
    Route::delete('applications/{application}/residential-addresses/{residentialAddress}', [ResidentialAddressController::class, 'destroy'])
        ->name('applications.residential-addresses.destroy');

    // Employment Details
    Route::post('applications/{application}/employment-details', [EmploymentDetailsController::class, 'store'])
        ->name('applications.employment-details.store');
    Route::patch('applications/{application}/employment-details/{employmentDetail}', [EmploymentDetailsController::class, 'update'])
        ->name('applications.employment-details.update');
    Route::delete('applications/{application}/employment-details/{employmentDetail}', [EmploymentDetailsController::class, 'destroy'])
        ->name('applications.employment-details.destroy');

    // Living Expenses
    Route::post('applications/{application}/living-expenses', [LivingExpenseController::class, 'store'])
        ->name('applications.living-expenses.store');
    Route::patch('applications/{application}/living-expenses/{livingExpense}', [LivingExpenseController::class, 'update'])
        ->name('applications.living-expenses.update');
    Route::delete('applications/{application}/living-expenses/{livingExpense}', [LivingExpenseController::class, 'destroy'])
        ->name('applications.living-expenses.destroy');

    // Documents
    Route::post('applications/{application}/documents', [DocumentController::class, 'store'])
        ->name('applications.documents.store');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])
        ->name('documents.download');
    Route::delete('applications/{application}/documents/{document}', [DocumentController::class, 'destroy'])
        ->name('applications.documents.destroy');

    // Questions (Client Answers)
    Route::post('questions/{question}/answer', [QuestionController::class, 'answer'])
        ->name('questions.answer');

    // Declarations
    Route::get('applications/{application}/declarations', [DeclarationController::class, 'index'])
        ->name('applications.declarations.index');
    Route::post('applications/{application}/declarations', [DeclarationController::class, 'store'])
        ->name('applications.declarations.store');
});

/*
|--------------------------------------------------------------------------
| Admin/Assessor Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'role:admin|assessor'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Applications
    Route::get('/applications', [AdminApplicationController::class, 'index'])
        ->name('applications.index');
    Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])
        ->name('applications.show');
    Route::patch('/applications/{application}/status', [AdminApplicationController::class, 'updateStatus'])
        ->name('applications.updateStatus');
    Route::patch('/applications/{application}/assign', [AdminApplicationController::class, 'assign'])
        ->name('applications.assign');
    Route::get('/applications/{application}/export-pdf', [AdminApplicationController::class, 'exportPdf'])
        ->name('applications.exportPdf');

    // Comments
    Route::post('applications/{application}/comments', [CommentController::class, 'store'])
        ->name('comments.store');
    Route::patch('comments/{comment}', [CommentController::class, 'update'])
        ->name('comments.update');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');
    Route::post('comments/{comment}/toggle-pin', [CommentController::class, 'togglePin'])
        ->name('comments.togglePin');

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('applications/{application}/tasks', [TaskController::class, 'store'])
        ->name('tasks.store');
    Route::patch('tasks/{task}', [TaskController::class, 'update'])
        ->name('tasks.update');
    Route::post('tasks/{task}/complete', [TaskController::class, 'complete'])
        ->name('tasks.complete');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])
        ->name('tasks.destroy');

    // Questions (Admin Asks)
    Route::post('applications/{application}/questions', [QuestionController::class, 'store'])
        ->name('questions.store');
    Route::delete('questions/{question}', [QuestionController::class, 'destroy'])
        ->name('questions.destroy');

    // Communications
    Route::get('applications/{application}/communications', [CommunicationController::class, 'index'])
        ->name('communications.index');
    Route::post('applications/{application}/send-email', [CommunicationController::class, 'sendEmail'])
        ->name('communications.sendEmail');
    Route::post('applications/{application}/send-sms', [CommunicationController::class, 'sendSms'])
        ->name('communications.sendSms');
    Route::get('communications/{communication}', [CommunicationController::class, 'show'])
        ->name('communications.show');

    // Credit Checks
    Route::post('applications/{application}/credit-check', [CreditCheckController::class, 'request'])
        ->name('creditChecks.request');
    Route::patch('credit-checks/{creditCheck}', [CreditCheckController::class, 'update'])
        ->name('creditChecks.update');
    Route::get('credit-checks/{creditCheck}', [CreditCheckController::class, 'show'])
        ->name('creditChecks.show');

    // Living Expense Verification
    Route::patch('living-expenses/{livingExpense}/verify', [LivingExpenseController::class, 'verify'])
        ->name('livingExpenses.verify');

    // Document Review
    Route::patch('documents/{document}/status', [DocumentController::class, 'updateStatus'])
        ->name('documents.updateStatus');
});


/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    // Residential Addresses
    Route::get('/api/suburbs/{state}', function ($state) {
        return response()->json(\App\Helpers\AustralianSuburbs::getSuburbsByState($state));
    })->name('api.suburbs');
});

// WhatsApp & SMS Routes
Route::post('/whatsapp/send', [WhatsAppController::class, 'send'])->name('whatsapp.send');
Route::post('/whatsapp/queue', [WhatsAppController::class, 'queue'])->name('whatsapp.queue');
Route::post('/sms/send', [WhatsAppController::class, 'sendSMS'])->name('sms.send');
Route::post('/sms/queue', [WhatsAppController::class, 'queueSMS'])->name('sms.queue');

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
