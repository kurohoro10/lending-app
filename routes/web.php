<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PersonalDetailsController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Authenticated Client Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        if (auth()->user()->canAccessAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('applications.index');
    })->name('dashboard');

    // Client Applications
    Route::resource('applications', ApplicationController::class);
    Route::post('applications/{application}/submit', [ApplicationController::class, 'submit'])
        ->name('applications.submit');

    // Application Components
    Route::post('applications/{application}/personal-details', [PersonalDetailsController::class, 'store'])
        ->name('applications.personal-details.store');

    // TODO: Add routes for other application components
    // Route::resource('applications.residential-addresses', ResidentialAddressController::class);
    // Route::resource('applications.employment-details', EmploymentDetailsController::class);
    // Route::resource('applications.living-expenses', LivingExpenseController::class);
    // Route::resource('applications.documents', DocumentController::class);
    // Route::resource('applications.questions', QuestionController::class);
    // Route::post('applications/{application}/declarations', [DeclarationController::class, 'store']);
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
    Route::get('/dashboard', function () {
        $stats = [
            'total_applications' => \App\Models\Application::count(),
            'pending_review' => \App\Models\Application::where('status', 'submitted')->count(),
            'under_review' => \App\Models\Application::where('status', 'under_review')->count(),
            'approved' => \App\Models\Application::where('status', 'approved')->count(),
        ];
        return view('admin.dashboard', compact('stats'));
    })->name('dashboard');

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

    // TODO: Add admin routes for other features
    // Route::resource('applications.comments', CommentController::class);
    // Route::resource('applications.tasks', TaskController::class);
    // Route::post('applications/{application}/questions', [QuestionController::class, 'store']);
    // Route::post('applications/{application}/communications', [CommunicationController::class, 'store']);
    // Route::post('applications/{application}/credit-check', [CreditCheckController::class, 'request']);
    // Route::patch('living-expenses/{livingExpense}/verify', [LivingExpenseController::class, 'verify']);
});
