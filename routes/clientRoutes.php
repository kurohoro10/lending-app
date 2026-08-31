<?php
// routes/clientRoutes.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PersonalDetailsController;
use App\Http\Controllers\ResidentialAddressController;
use App\Http\Controllers\EmploymentDetailsController;
use App\Http\Controllers\LivingExpenseController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\GuarantorFormController;
use App\Http\Controllers\BusinessDeclarationController;
use App\Http\Controllers\LoanDeedController;
use App\Http\Controllers\DocumentSigningController;
use App\Http\Controllers\DeclarationController;
use App\Http\Controllers\CreditControllers\BasiqController;
use App\Http\Controllers\CreditControllers\CreditSenseController;
use App\Http\Controllers\BorrowerInformationController;
use App\Http\Controllers\BorrowerDirectorController;
use App\Http\Controllers\DirectorAssetsLiabilitiesController;
use App\Http\Controllers\CompanyAssetsLiabilitiesController;
use App\Http\Controllers\AccountantDetailController;
use App\Http\Controllers\TaskResponseController;

// Dashboard - Redirect based on role
Route::get('/dashboard', function () {
    if (auth()->user()->canAccessAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('applications.index');
})->name('dashboard');

// Client Applications
Route::get('applications',                    [ApplicationController::class, 'index'])->name('applications.index');
Route::get('applications/{application}',      [ApplicationController::class, 'show'])->name('applications.show');
Route::get('applications/{application}/edit', [ApplicationController::class, 'edit'])->name('applications.edit');
Route::patch('applications/{application}',    [ApplicationController::class, 'update'])->name('applications.update');
Route::delete('applications/{application}',   [ApplicationController::class, 'destroy'])->name('applications.destroy');
Route::post('applications/{application}/submit', [ApplicationController::class, 'submit'])->name('applications.submit');

// Basiq Bank Statement Connection
// Replaces the old: POST applications/{application}/bank-statements/complete
Route::prefix('applications/{application}/basiq')->name('basiq.')->group(function () {
    Route::post('user',         [BasiqController::class, 'createUser'])->name('user');
    Route::post('client-token', [BasiqController::class, 'createClientToken'])->name('client-token');
    Route::post('complete',     [BasiqController::class, 'complete'])->name('complete');
    Route::post('auth-link',    [BasiqController::class, 'createAuthLink'])->name('auth-link');
    Route::get('complete',      [BasiqController::class, 'completeRedirect'])->name('complete-redirect');
    
    // NEW: For polling in question context
    Route::get('check-completion', [BasiqController::class, 'checkCompletion'])->name('check-completion');
});

// Personal Details
Route::post('applications/{application}/personal-details', [PersonalDetailsController::class, 'store'])
    ->name('applications.personal-details.store');

// Residential Addresses
Route::get('/api/suburbs/{state}', function ($state) {
    return response()->json(\App\Helpers\AustralianSuburbs::getSuburbsByState($state));
})->name('api.suburbs');
Route::post('applications/{application}/residential-addresses',                        [ResidentialAddressController::class, 'store'])
    ->name('applications.residential-addresses.store');
Route::patch('applications/{application}/residential-addresses/{residentialAddress}',  [ResidentialAddressController::class, 'update'])
    ->name('applications.residential-addresses.update');
Route::delete('applications/{application}/residential-addresses/{residentialAddress}', [ResidentialAddressController::class, 'destroy'])
    ->name('applications.residential-addresses.destroy');

// Employment Details
Route::post('applications/{application}/employment-details',                     [EmploymentDetailsController::class, 'store'])
    ->name('applications.employment-details.store');
Route::patch('applications/{application}/employment-details/{employmentDetail}', [EmploymentDetailsController::class, 'update'])
    ->name('applications.employment-details.update');
Route::delete('applications/{application}/employment-details/{employmentDetail}',[EmploymentDetailsController::class, 'destroy'])
    ->name('applications.employment-details.destroy');

// Living Expenses
Route::post('applications/{application}/living-expenses',                  [LivingExpenseController::class, 'store'])
    ->name('applications.living-expenses.store');
Route::patch('applications/{application}/living-expenses/{livingExpense}', [LivingExpenseController::class, 'update'])
    ->name('applications.living-expenses.update');
Route::delete('applications/{application}/living-expenses/{livingExpense}',[LivingExpenseController::class, 'destroy'])
    ->name('applications.living-expenses.destroy');

// Documents
Route::post('applications/{application}/documents',             [DocumentController::class, 'store'])
    ->name('applications.documents.store');
Route::get('documents/{document}/download',                     [DocumentController::class, 'download'])
    ->name('documents.download');
Route::delete('applications/{application}/documents/{document}',[DocumentController::class, 'destroy'])
    ->name('applications.documents.destroy');

// Questions (Client Answers)
Route::post('questions/{question}/answer', [QuestionController::class, 'answer'])
    ->name('questions.answer');

// Declarations
Route::get('applications/{application}/declarations',  [DeclarationController::class, 'index'])
    ->name('applications.declarations.index');
Route::post('applications/{application}/declarations', [DeclarationController::class, 'store'])
    ->name('applications.declarations.store');

// Guarantor Form
Route::get('/applications/{application}/guarantor-form',       [GuarantorFormController::class, 'show'])
    ->name('applications.guarantor-form.client.show')
    ->middleware('signed');

Route::post('/applications/{application}/guarantor-form/sign', [GuarantorFormController::class, 'sign'])
    ->name('applications.guarantor-form.sign');

Route::get('/applications/{application}/business-declaration',
    [BusinessDeclarationController::class, 'show'])
    ->name('applications.business-declaration.show')
    ->middleware('signed');

Route::post('/applications/{application}/business-declaration/sign',
    [BusinessDeclarationController::class, 'sign'])
    ->name('applications.business-declaration.sign');

// Loan Deed
Route::get('/applications/{application}/loan-deed',       [LoanDeedController::class, 'show'])
    ->name('applications.loan-deed.client.show')
    ->middleware('signed');

Route::post('/applications/{application}/loan-deed/sign', [LoanDeedController::class, 'sign'])
    ->name('applications.loan-deed.sign');

// Document Signing
Route::get('/applications/{application}/document-signing', [DocumentSigningController::class, 'show'])
    ->name('applications.document-signing.client.show')
    ->middleware('signed');

Route::get('/applications/{application}/document-signing/file', [DocumentSigningController::class, 'streamFile'])
    ->name('applications.document-signing.client.file')
    ->middleware('signed');

Route::post('/applications/{application}/document-signing/sign', [DocumentSigningController::class, 'sign'])
    ->name('applications.document-signing.sign');

// CreditSense Bank Statement Connection
Route::prefix('applications/{application}/creditsense')->name('creditsense.')->group(function () {
    Route::get('config',    [CreditSenseController::class, 'iframeConfig'])->name('config');
    Route::post('complete', [CreditSenseController::class, 'complete'])->name('complete');
    Route::post('save-app-id', [CreditSenseController::class, 'saveAppId'])->name('saveAppId');
});

// Borrower
Route::post('applications/{application}/borrower-information',
    [BorrowerInformationController::class, 'store']
)->name('applications.borrower-information.store');

// Borrower Director
Route::prefix('applications/{application}/borrower-directors')->name('applications.directors.')->group(function () {
    Route::post('/',                [BorrowerDirectorController::class, 'store'])->name('store');
    Route::patch('{director}',      [BorrowerDirectorController::class, 'update'])->name('update');
    Route::delete('{director}',     [BorrowerDirectorController::class, 'destroy'])->name('destroy');
});

// Director Assests and Liabilities
Route::prefix('applications/{application}')->name('applications.')->group(function () {

    // Assets
    Route::post('director-assets',          [DirectorAssetsLiabilitiesController::class, 'storeAsset'])->name('assets.store');
    Route::patch('director-assets/{asset}', [DirectorAssetsLiabilitiesController::class, 'updateAsset'])->name('assets.update'); 
    Route::delete('director-assets/{asset}',[DirectorAssetsLiabilitiesController::class, 'destroyAsset'])->name('assets.destroy');

    // Liabilities
    Route::post('director-liabilities',                  [DirectorAssetsLiabilitiesController::class, 'storeLiability'])->name('liabilities.store');
    Route::patch('director-liabilities/{liability}',     [DirectorAssetsLiabilitiesController::class, 'updateLiability'])->name('liabilities.update'); 
    Route::delete('director-liabilities/{liability}',    [DirectorAssetsLiabilitiesController::class, 'destroyLiability'])->name('liabilities.destroy');
});

Route::prefix('applications/{application}')->name('applications.')->group(function () {

    // Company Assets
    Route::post('company-assets',               [CompanyAssetsLiabilitiesController::class, 'storeAsset'])->name('company-assets.store');
    Route::delete('company-assets/{asset}',     [CompanyAssetsLiabilitiesController::class, 'destroyAsset'])->name('company-assets.destroy');

    // Company Liabilities
    Route::post('company-liabilities',              [CompanyAssetsLiabilitiesController::class, 'storeLiability'])->name('company-liabilities.store');
    Route::delete('company-liabilities/{liability}',[CompanyAssetsLiabilitiesController::class, 'destroyLiability'])->name('company-liabilities.destroy');
});

Route::get('applications/{application}/download-confirmation',
    [ApplicationController::class, 'downloadConfirmation']
)->name('applications.download-confirmation');

// Accountant Details
Route::post(
    'applications/{application}/accountant-details',
    [AccountantDetailController::class, 'store']
)->name('applications.accountant-details.store');

Route::get('tasks/{task}/respond',  [TaskResponseController::class, 'show'])
    ->name('tasks.respond.show');
Route::post('tasks/{task}/respond', [TaskResponseController::class, 'store'])
    ->name('tasks.respond.store');
