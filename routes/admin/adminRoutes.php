<?php
// routes/admin/adminRoutes.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\CreditControllers\CreditCheckController;
use App\Http\Controllers\Admin\LivingExpenseVerificationController;
use App\Http\Controllers\LivingExpenseController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\Question\QuestionController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\Communication\EmailCommunicationController;
use App\Http\Controllers\Admin\Communication\SmsCommunicationController;
use App\Http\Controllers\Admin\CreditControllers\CreditSenseController;
use App\Http\Controllers\Admin\CommunicationController;

/*
|--------------------------------------------------------------------------
| Admin/Assessor Routes
|--------------------------------------------------------------------------
*/
// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Applications
Route::get('/applications',                                 [ApplicationController::class, 'index'])
    ->name('applications.index');
Route::get('/applications/{application}',                   [ApplicationController::class, 'show'])
    ->name('applications.show');
Route::patch('/applications/{application}/status',          [ApplicationController::class, 'updateStatus'])
    ->name('applications.updateStatus');
Route::patch('/applications/{application}/assign',          [ApplicationController::class, 'assign'])
    ->name('applications.assign');
Route::get('/applications/{application}/export-pdf',        [ApplicationController::class, 'exportPdf'])
    ->name('applications.exportPdf');
Route::post('/applications/{application}/return-to-client', [ApplicationController::class, 'returnToClient'])
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
Route::patch('comments/{commentId}/restore',       [CommentController::class, 'restore'])
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
Route::patch('questions/{question}/mark-read', [QuestionController::class, 'markAsRead'])
->name('questions.markAsRead');

// Email
Route::prefix('applications/{application}')->group(function () {
    Route::get('emails/poll', [EmailCommunicationController::class, 'poll'])->name('email.poll');
    Route::get('sms/poll',    [SmsCommunicationController::class,   'poll'])->name('sms.poll');

    Route::get('email-templates',  [EmailCommunicationController::class, 'getTemplates'])->name('email.templates');
    Route::post('send-email',      [EmailCommunicationController::class, 'send'])->name('email.send');
    Route::get('emails',           [EmailCommunicationController::class, 'index'])->name('email.index');
    Route::patch('emails/{communication}/read', [EmailCommunicationController::class, 'markRead'])->name('email.markRead');

    // SMS
    Route::get('sms-templates',    [SmsCommunicationController::class, 'getTemplates'])->name('sms.templates');
    Route::post('send-sms',        [SmsCommunicationController::class, 'send'])->name('sms.send');
    Route::get('sms',              [SmsCommunicationController::class, 'index'])->name('sms.index');
    Route::patch('sms/{communication}/read', [SmsCommunicationController::class, 'markRead'])->name('sms.markRead');

    // Manual inbound email logging (admin use)
    Route::post('email-incoming',  [EmailCommunicationController::class, 'incoming'])->name('email.incoming');
});

Route::post('applications/{application}/communications/mark-read',
    [CommunicationController::class, 'markChannelRead'])
    ->name('applications.communications.markChannelRead');

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
Route::get('applications/{application}/expenses/data',   [LivingExpenseVerificationController::class, 'data'])
    ->name('expenses.data');
Route::post('applications/{application}/expenses/verify', [LivingExpenseVerificationController::class, 'store'])
    ->name('expenses.verify');

// Document Review
Route::patch('documents/{document}/status', [DocumentController::class, 'updateStatus'])
    ->name('documents.update-status');

// Settings test connections
Route::post('settings/basiq/test-connection',[SettingsController::class, 'testBasiqConnection'])
    ->name('settings.basiq.test-connection');

Route::post('settings/creditsense/test-connection', [SettingsController::class, 'testCreditSenseConnection'])
    ->name('settings.creditsense.test-connection');

// Settings
Route::get('/settings',           [SettingsController::class, 'index'])
    ->name('settings.index');
Route::patch('/settings/{group}', [SettingsController::class, 'update'])
    ->name('settings.update');


// CreditSense
Route::post('applications/{application}/creditsense/fetch-report', [CreditSenseController::class, 'fetchReport'])
    ->name('creditsense.fetchReport');
Route::post('applications/{application}/creditsense/quicklink',    [CreditSenseController::class, 'createQuicklink'])
    ->name('creditsense.quicklink');
