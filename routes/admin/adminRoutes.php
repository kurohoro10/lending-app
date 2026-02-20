<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\CommunicationController;
use App\Http\Controllers\Admin\CreditCheckController;
use App\Http\Controllers\LivingExpenseController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Question\QuestionController;
use App\Http\Controllers\Admin\SettingsController;

/*
|--------------------------------------------------------------------------
| Admin/Assessor Routes
|--------------------------------------------------------------------------
*/
// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Applications
Route::get('/applications',                                 [AdminApplicationController::class, 'index'])
    ->name('applications.index');
Route::get('/applications/{application}',                   [AdminApplicationController::class, 'show'])
    ->name('applications.show');
Route::patch('/applications/{application}/status',          [AdminApplicationController::class, 'updateStatus'])
    ->name('applications.updateStatus');
Route::patch('/applications/{application}/assign',          [AdminApplicationController::class, 'assign'])
    ->name('applications.assign');
Route::get('/applications/{application}/export-pdf',        [AdminApplicationController::class, 'exportPdf'])
    ->name('applications.exportPdf');
Route::post('/applications/{application}/return-to-client', [AdminApplicationController::class, 'returnToClient'])
->name('applications.returnToClient');

// Comments
Route::post('applications/{application}/comments', [CommentController::class, 'store'])
    ->name('comments.store');
Route::patch('comments/{comment}',                 [CommentController::class, 'update'])
    ->name('comments.update');
Route::delete('comments/{comment}',                [CommentController::class, 'destroy'])
    ->name('comments.destroy');
Route::post('comments/{comment}/toggle-pin',       [CommentController::class, 'togglePin'])
    ->name('comments.togglePin');
Route::patch('/{commentId}/restore',               [CommentController::class, 'restore'])
    ->name('comments.restore');

// Tasks
Route::get('/tasks',                            [TaskController::class, 'index'])->name('tasks.index');
Route::post('applications/{application}/tasks', [TaskController::class, 'store'])
    ->name('tasks.store');
Route::patch('tasks/{task}',                    [TaskController::class, 'update'])
    ->name('tasks.update');
Route::post('tasks/{task}/complete',            [TaskController::class, 'complete'])
    ->name('tasks.complete');
Route::delete('tasks/{task}',                   [TaskController::class, 'destroy'])
    ->name('tasks.destroy');

// Questions (Admin Asks)
Route::post('applications/{application}/questions', [QuestionController::class, 'store'])
    ->name('questions.store');
Route::delete('questions/{question}',               [QuestionController::class, 'destroy'])
    ->name('questions.destroy');

// Communications
Route::get('applications/{application}/communications', [CommunicationController::class, 'index'])
    ->name('communications.index');
Route::post('applications/{application}/send-email',    [CommunicationController::class, 'sendEmail'])
    ->name('communications.sendEmail');
Route::post('applications/{application}/send-sms',      [CommunicationController::class, 'sendSms'])
    ->name('communications.sendSms');
Route::get('communications/{communication}',            [CommunicationController::class, 'show'])
    ->name('communications.show');

// Credit Checks
Route::post('applications/{application}/credit-check', [CreditCheckController::class, 'request'])
    ->name('creditChecks.request');
Route::patch('credit-checks/{creditCheck}',            [CreditCheckController::class, 'update'])
    ->name('creditChecks.update');
Route::get('credit-checks/{creditCheck}',              [CreditCheckController::class, 'show'])
    ->name('creditChecks.show');

// Living Expense Verification
Route::patch('living-expenses/{livingExpense}/verify', [LivingExpenseController::class, 'verify'])
    ->name('livingExpenses.verify');

// Document Review
Route::patch('documents/{document}/status', [DocumentController::class, 'updateStatus'])
    ->name('documents.updateStatus');

// Communication Templates & Sending
Route::get('applications/{application}/email-templates', [CommunicationController::class, 'getEmailTemplates'])
    ->name('communications.emailTemplates');
Route::get('applications/{application}/sms-templates',   [CommunicationController::class, 'getSMSTemplates'])
    ->name('communications.smsTemplates');
Route::post('applications/{application}/send-email',     [CommunicationController::class, 'sendEmail'])
    ->name('communications.sendEmail');
Route::post('applications/{application}/send-sms',       [CommunicationController::class, 'sendSms'])
    ->name('communications.sendSms');

    // Settings
Route::get('/settings',           [SettingsController::class, 'index'])
    ->name('settings.index');
Route::patch('/settings/{group}', [SettingsController::class, 'update'])
    ->name('settings.update');
