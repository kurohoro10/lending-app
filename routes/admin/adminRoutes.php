<?php
// routes/admin/adminRoutes.php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use App\Actions\Application\GenerateSubmissionPdf;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\GuarantorFormController;
use App\Http\Controllers\Admin\LoanDeedController;
use App\Http\Controllers\Admin\BusinessDeclarationController;
use App\Http\Controllers\Admin\DocumentSigningController;
use App\Http\Controllers\Admin\CreditControllers\CreditCheckController;
use App\Http\Controllers\Admin\LivingExpenseVerificationController;
use App\Http\Controllers\Admin\AssessorDirectorAssetsLiabilitiesController;
use App\Http\Controllers\Admin\AssessorEmploymentController;
use App\Http\Controllers\LivingExpenseController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\Question\QuestionController;
use App\Http\Controllers\Admin\Question\AssessmentChecklistController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\Communication\EmailCommunicationController;
use App\Http\Controllers\Admin\Communication\SmsCommunicationController;
use App\Http\Controllers\Admin\Communication\AdHocCommunicationController;
use App\Http\Controllers\Admin\Communication\EmailDirectorsController;
use App\Http\Controllers\Admin\CreditControllers\CreditSenseController;
use App\Http\Controllers\Admin\CommunicationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SubmissionPdfController;


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
Route::get('/applications/{application}/activity-log',      [ApplicationController::class, 'activityLog'])
    ->name('applications.activityLog');
Route::patch('/applications/{application}/status',          [ApplicationController::class, 'updateStatus'])
    ->name('applications.updateStatus');
Route::patch('/applications/{application}/assign',          [ApplicationController::class, 'assign'])
    ->name('applications.assign');
Route::get('/applications/{application}/export-pdf',        [ApplicationController::class, 'exportPdf'])
    ->name('applications.exportPdf');
Route::post('/applications/{application}/return-to-client', [ApplicationController::class, 'returnToClient'])
->name('applications.returnToClient');

Route::post('/applications/{application}/generate-guarantor-form',
    [ApplicationController::class, 'generateGuarantorForm'])
    ->name('applications.generateGuarantorForm');

Route::get('/applications/{application}/download-guarantor-form',
    [ApplicationController::class, 'downloadGuarantorForm'])
    ->name('applications.downloadGuarantorForm');

// Guarantor Form
Route::get('/applications/{application}/guarantor-form',       [GuarantorFormController::class, 'show'])
    ->name('applications.guarantor-form.show');
Route::post('/applications/{application}/guarantor-form',      [GuarantorFormController::class, 'store'])
    ->name('applications.guarantor-form.store');
Route::post('/applications/{application}/guarantor-form/send', [GuarantorFormController::class, 'send'])
    ->name('applications.guarantor-form.send');
Route::get('/applications/{application}/guarantor-form/signed', [GuarantorFormController::class, 'viewSigned'])
    ->name('applications.guarantor-form.signed');
Route::patch('/applications/{application}/guarantor-required', [GuarantorFormController::class, 'toggleGuarantorRequired'])
    ->name('applications.guarantor-required.toggle');

// Loan Deed
Route::get('/applications/{application}/loan-deed',         [LoanDeedController::class, 'show'])
    ->name('applications.loan-deed.show');
Route::post('/applications/{application}/loan-deed',        [LoanDeedController::class, 'store'])
    ->name('applications.loan-deed.store');
Route::post('/applications/{application}/loan-deed/send',   [LoanDeedController::class, 'send'])
    ->name('applications.loan-deed.send');
Route::get('/applications/{application}/loan-deed/signed',  [LoanDeedController::class, 'viewSigned'])
    ->name('applications.loan-deed.signed');
Route::get('/applications/{application}/loan-deed/pdf',     [LoanDeedController::class, 'downloadPdf'])
    ->name('applications.loan-deed.pdf');

// Business Declaration
Route::get('/applications/{application}/business-declaration',
    [BusinessDeclarationController::class, 'show'])
    ->name('applications.business-declaration.show');

Route::post('/applications/{application}/business-declaration',
    [BusinessDeclarationController::class, 'store'])
    ->name('applications.business-declaration.store');

Route::post('/applications/{application}/business-declaration/send',
    [BusinessDeclarationController::class, 'send'])
    ->name('applications.business-declaration.send');

Route::get('/applications/{application}/business-declaration/signed',
    [BusinessDeclarationController::class, 'view'])
    ->name('applications.business-declaration.view');

Route::get('/applications/{application}/business-declaration/pdf',
    [BusinessDeclarationController::class, 'downloadPdf'])
    ->name('applications.business-declaration.pdf');

// Document Signing
Route::get('/applications/{application}/document-signing',
    [DocumentSigningController::class, 'show'])
    ->name('applications.document-signing.show');

Route::post('/applications/{application}/document-signing',
    [DocumentSigningController::class, 'store'])
    ->name('applications.document-signing.store');

Route::post('/applications/{application}/document-signing/send',
    [DocumentSigningController::class, 'send'])
    ->name('applications.document-signing.send');

Route::get('/applications/{application}/document-signing/signed',
    [DocumentSigningController::class, 'viewSigned'])
    ->name('applications.document-signing.view');

Route::get('/applications/{application}/document-signing/pdf',
    [DocumentSigningController::class, 'downloadPdf'])
    ->name('applications.document-signing.pdf');

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
Route::post('tasks/{task}/send-to-client',      [TaskController::class, 'sendToClient'])->name('tasks.sendToClient');

// Questions (Admin Asks) — freeform, kept as a secondary option
Route::post('applications/{application}/questions', [QuestionController::class, 'store'])
    ->name('questions.store');
Route::delete('questions/{question}',               [QuestionController::class, 'destroy'])
    ->name('questions.destroy');
Route::patch('questions/{question}/mark-read', [QuestionController::class, 'markAsRead'])
    ->name('questions.markAsRead');
Route::patch('questions/{question}/approve', [QuestionController::class, 'approve'])
    ->name('questions.approve');
Route::patch('questions/{question}/return',  [QuestionController::class, 'return'])
    ->name('questions.return');

// Assessment Checklist (Admin bulk-requests documents)
Route::get('applications/{application}/assessment-checklist/preview', [AssessmentChecklistController::class, 'preview'])
    ->name('assessmentChecklist.preview');
Route::post('applications/{application}/assessment-checklist/send',   [AssessmentChecklistController::class, 'send'])
    ->name('assessmentChecklist.send');

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

Route::post('/applications/{application}/email-directors', [EmailDirectorsController::class, 'send'])
    ->name('applications.email-directors.send');

// Ad-hoc (freeform recipient) communications
Route::prefix('applications/{application}')->group(function () {
    // Templates
    Route::get('ad-hoc/email-templates', [AdHocCommunicationController::class, 'emailTemplates'])
        ->name('ad-hoc.email.templates');
    Route::get('ad-hoc/sms-templates',   [AdHocCommunicationController::class, 'smsTemplates'])
        ->name('ad-hoc.sms.templates');
 
    // Send
    Route::post('ad-hoc/send-email', [AdHocCommunicationController::class, 'sendEmail'])
        ->name('ad-hoc.email.send');
    Route::post('ad-hoc/send-sms',   [AdHocCommunicationController::class, 'sendSms'])
        ->name('ad-hoc.sms.send');
});

// Assessor Director Assets & Liabilities
Route::post('applications/{application}/assessor-dal/unlock',
    [AssessorDirectorAssetsLiabilitiesController::class, 'unlock'])
    ->name('assessor-dal.unlock');

Route::post('applications/{application}/assessor-dal/stamp',
    [AssessorDirectorAssetsLiabilitiesController::class, 'stamp'])
    ->name('assessor-dal.stamp');

Route::post('applications/{application}/assessor-dal/assets',
    [AssessorDirectorAssetsLiabilitiesController::class, 'storeAsset'])
    ->name('assessor-dal.assets.store');

Route::patch('assessor-dal/assets/{asset}',
    [AssessorDirectorAssetsLiabilitiesController::class, 'updateAsset'])
    ->name('assessor-dal.assets.update');

Route::delete('assessor-dal/assets/{asset}',
    [AssessorDirectorAssetsLiabilitiesController::class, 'destroyAsset'])
    ->name('assessor-dal.assets.destroy');

Route::post('applications/{application}/assessor-dal/liabilities',
    [AssessorDirectorAssetsLiabilitiesController::class, 'storeLiability'])
    ->name('assessor-dal.liabilities.store');

Route::patch('assessor-dal/liabilities/{liability}',
    [AssessorDirectorAssetsLiabilitiesController::class, 'updateLiability'])
    ->name('assessor-dal.liabilities.update');

Route::delete('assessor-dal/liabilities/{liability}',
    [AssessorDirectorAssetsLiabilitiesController::class, 'destroyLiability'])
    ->name('assessor-dal.liabilities.destroy');

Route::post('applications/{application}/assessor-employment/unlock',
    [AssessorEmploymentController::class, 'unlock'])
    ->name('assessor-employment.unlock');

Route::post('applications/{application}/assessor-employment/stamp',
    [AssessorEmploymentController::class, 'stamp'])
    ->name('assessor-employment.stamp');

Route::post('applications/{application}/assessor-employment',
    [AssessorEmploymentController::class, 'store'])
    ->name('assessor-employment.store');

Route::patch('applications/{application}/assessor-employment/{employment}',
    [AssessorEmploymentController::class, 'update'])
    ->name('assessor-employment.update');

Route::delete('applications/{application}/assessor-employment/{employment}',
    [AssessorEmploymentController::class, 'destroy'])
    ->name('assessor-employment.destroy');

Route::post('applications/{application}/assessor-employment/{employment}/upload',
    [AssessorEmploymentController::class, 'uploadDocument'])
    ->name('assessor-employment.upload');

Route::get('assessor-employment-documents/{document}/download',
    [AssessorEmploymentController::class, 'downloadDocument'])
    ->name('assessor-employment.documents.download');

Route::delete('assessor-employment-documents/{document}',
    [AssessorEmploymentController::class, 'destroyDocument'])
    ->name('assessor-employment.documents.destroy');

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

Route::get('/users',                    [UserController::class, 'index'])
    ->name('users.index');
Route::patch('/users/{user}/unlock',    [UserController::class, 'unlock'])
    ->name('users.unlock');

Route::post('applications/{application}/creditsense/upload-report',
    [CreditSenseController::class, 'uploadReport'])
    ->name('creditsense.uploadReport');

Route::get('submissions/{filename}', function ($filename) {
    abort_unless(str_ends_with($filename, '.pdf'), 404);
    $path = GenerateSubmissionPdf::FOLDER . '/' . $filename;
    abort_unless(
        Storage::disk(GenerateSubmissionPdf::DISK)->exists($path),
        404
    );
    return Storage::disk(GenerateSubmissionPdf::DISK)->download($path);
})->name('submissions.download');
