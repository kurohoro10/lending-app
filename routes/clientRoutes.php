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
use App\Http\Controllers\DeclarationController;
use App\Http\Controllers\CreditControllers\BasiqController;
use App\Http\Controllers\CreditControllers\CreditSenseController;
use App\Http\Controllers\BorrowerInformationController;
use App\Http\Controllers\BorrowerDirectorController;
use App\Http\Controllers\DirectorAssetsLiabilitiesController;
use App\Http\Controllers\CompanyAssetsLiabilitiesController;
use App\Http\Controllers\AccountantDetailController;

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
    Route::post('auth-link', [BasiqController::class, 'createAuthLink'])
    ->name('auth-link');
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

// CreditSense Bank Statement Connection
Route::prefix('applications/{application}/creditsense')->name('creditsense.')->group(function () {
    Route::get('config',    [CreditSenseController::class, 'iframeConfig'])->name('config');
    Route::post('complete', [CreditSenseController::class, 'complete'])->name('complete');
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
    Route::delete('director-assets/{asset}',[DirectorAssetsLiabilitiesController::class, 'destroyAsset'])->name('assets.destroy');

    // Liabilities
    Route::post('director-liabilities',                  [DirectorAssetsLiabilitiesController::class, 'storeLiability'])->name('liabilities.store');
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

// Accountant Details
Route::post(
    'applications/{application}/accountant-details',
    [AccountantDetailController::class, 'store']
)->name('applications.accountant-details.store');
