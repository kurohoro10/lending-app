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

// Dashboard - Redirect based on role
Route::get('/dashboard', function () {
    if (auth()->user()->canAccessAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('applications.index');
})->name('dashboard');

// Client Applications
Route::get('applications',                                          [ApplicationController::class, 'index'])->name('applications.index');
Route::get('applications/{application}',                            [ApplicationController::class, 'show'])->name('applications.show');
Route::get('applications/{application}/edit',                       [ApplicationController::class, 'edit'])->name('applications.edit');
Route::patch('applications/{application}',                          [ApplicationController::class, 'update'])->name('applications.update');
Route::delete('applications/{application}',                         [ApplicationController::class, 'destroy'])->name('applications.destroy');
Route::post('applications/{application}/submit',                    [ApplicationController::class, 'submit'])
    ->name('applications.submit');
Route::post('/applications/{application}/bank-statements/complete', [ApplicationController::class, 'completeBankStatements'])
    ->name('applications.bank-statements.complete');

// Personal Details
Route::post('applications/{application}/personal-details', [PersonalDetailsController::class, 'store'])
    ->name('applications.personal-details.store');

// Residential Addresses
Route::get('/api/suburbs/{state}', function ($state) {
    return response()->json(\App\Helpers\AustralianSuburbs::getSuburbsByState($state));
})->name('api.suburbs');
Route::post('applications/{application}/residential-addresses',                         [ResidentialAddressController::class, 'store'])
    ->name('applications.residential-addresses.store');
Route::patch('applications/{application}/residential-addresses/{residentialAddress}',   [ResidentialAddressController::class, 'update'])
    ->name('applications.residential-addresses.update');
Route::delete('applications/{application}/residential-addresses/{residentialAddress}',  [ResidentialAddressController::class, 'destroy'])
    ->name('applications.residential-addresses.destroy');

// Employment Details
Route::post('applications/{application}/employment-details',                      [EmploymentDetailsController::class, 'store'])
    ->name('applications.employment-details.store');
Route::patch('applications/{application}/employment-details/{employmentDetail}',  [EmploymentDetailsController::class, 'update'])
    ->name('applications.employment-details.update');
Route::delete('applications/{application}/employment-details/{employmentDetail}', [EmploymentDetailsController::class, 'destroy'])
    ->name('applications.employment-details.destroy');

// Living Expenses
Route::post('applications/{application}/living-expenses',                   [LivingExpenseController::class, 'store'])
    ->name('applications.living-expenses.store');
Route::patch('applications/{application}/living-expenses/{livingExpense}',  [LivingExpenseController::class, 'update'])
    ->name('applications.living-expenses.update');
Route::delete('applications/{application}/living-expenses/{livingExpense}', [LivingExpenseController::class, 'destroy'])
    ->name('applications.living-expenses.destroy');

// Documents
Route::post('applications/{application}/documents',              [DocumentController::class, 'store'])
    ->name('applications.documents.store');
Route::get('documents/{document}/download',                      [DocumentController::class, 'download'])
    ->name('documents.download');
Route::delete('applications/{application}/documents/{document}', [DocumentController::class, 'destroy'])
    ->name('applications.documents.destroy');

// Questions (Client Answers)
Route::post('questions/{question}/answer', [QuestionController::class, 'answer'])
    ->name('questions.answer');

// Declarations
Route::get('applications/{application}/declarations',  [DeclarationController::class, 'index'])
    ->name('applications.declarations.index');
Route::post('applications/{application}/declarations', [DeclarationController::class, 'store'])
    ->name('applications.declarations.store');
