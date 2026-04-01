<?php

/**
 * @file    app/Http/Controllers/Admin/Question/QuestionController.php
 * @package App\Http\Controllers\Admin\Question
 *
 * Manages the lifecycle of questions raised by admins and assessors against
 * loan applications within the commercial loan application system.
 *
 * Responsibilities:
 *  - Creating questions and notifying the client via email and SMS
 *  - Deleting questions and logging the activity
 *  - Marking answered questions as read by an admin
 *  - Formatting question data for JSON API responses
 *
 * Route protection:
 *  - All routes in this controller are guarded by role middleware upstream;
 *    no additional policy checks are required within the controller itself.
 *
 * Supported document category hints (doc_category_hint):
 *  id | income | bank | assets | liabilities | employment | other | bank_connect
 *
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\Admin\Question;

use App\Helpers\DateFormatter;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Question;
use App\Notifications\Application\QuestionAsked;
use App\Services\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{
    /**
     * Valid document category hint values accepted on question creation.
     *
     * Centralised here so the validation rule and any downstream logic
     * share a single source of truth.
     *
     * @var string[]
     */
    private const DOC_CATEGORY_HINTS = [
        'id', 'income', 'bank', 'assets',
        'liabilities', 'employment', 'other', 'bank_connect',
    ];

    // =========================================================================
    // Question Creation
    // =========================================================================

    /**
     * Store a new question against an application and notify the client.
     *
     * Creates the question record, eager-loads the asking admin, logs the
     * activity, then dispatches both an email notification and (if a mobile
     * number is available) an SMS to the client. Notification failures are
     * caught and logged without rolling back the question creation.
     *
     * Route is protected by role middleware — no additional policy check needed.
     *
     * @param  Request      $request      Incoming HTTP request with question payload.
     * @param  Application  $application  The bound application model instance.
     * @return JsonResponse               The newly created question or a validation error.
     *
     * @bodyParam string  question          required  Question text (max 1000 chars).
     * @bodyParam boolean is_mandatory      nullable  Whether a response is required. Defaults to false.
     * @bodyParam string  doc_category_hint nullable  Document category hint for the client portal.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Question sent to client.",
     *   "question": { "id": 1, "question": "...", ... }
     * }
     */
    public function store(Request $request, Application $application): JsonResponse
    {
        $validated = $this->validateQuestionPayload($request);

        $question = $this->createQuestion($application, $validated);

        ActivityLog::logActivity('question_asked', 'Question asked to client', $application);

        $this->notifyClientOfQuestion($question, $application);

        return response()->json([
            'success'  => true,
            'message'  => 'Question sent to client.',
            'question' => $this->formatQuestion($question),
        ]);
    }

    // =========================================================================
    // Question Deletion
    // =========================================================================

    /**
     * Delete a question from an application and log the activity.
     *
     * Resolves the parent application from the question relationship before
     * deletion so it remains available for activity logging.
     *
     * Route is protected by role middleware — no additional policy check needed.
     *
     * @param  Question  $question  The bound question model instance.
     * @return JsonResponse         Simple success acknowledgement.
     *
     * @response 200 { "success": true, "message": "Question deleted." }
     */
    public function destroy(Question $question): JsonResponse
    {
        $application = $question->application;

        $question->delete();

        ActivityLog::logActivity('question_deleted', 'Question deleted', $application);

        return response()->json([
            'success' => true,
            'message' => 'Question deleted.',
        ]);
    }

    // =========================================================================
    // Read Status
    // =========================================================================

    /**
     * Manually mark an answered question as read by the current admin.
     *
     * Only questions in the `answered` status may be marked as read. Returns
     * the formatted read timestamp and the name of the admin who marked it.
     *
     * @param  Question  $question  The bound question model instance.
     * @return JsonResponse         Read timestamp and actor name, or a 400 error.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Question marked as read.",
     *   "read_at": "19 Mar 2026, 2:30pm",
     *   "read_by": "Jane Admin"
     * }
     * @response 400 { "success": false, "message": "Only answered questions can be marked as read." }
     */
    public function markAsRead(Question $question): JsonResponse
    {
        if ($question->status !== 'answered') {
            return response()->json([
                'success' => false,
                'message' => 'Only answered questions can be marked as read.',
            ], 400);
        }

        $question->markAsRead(auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Question marked as read.',
            'read_at' => DateFormatter::datetime($question->read_at),
            'read_by' => $question->readBy->name,
        ]);
    }

    // =========================================================================
    // Private Helpers — Validation & Persistence
    // =========================================================================

    /**
     * Validate the incoming question creation payload.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array              Validated question, is_mandatory, and doc_category_hint fields.
     */
    private function validateQuestionPayload(Request $request): array
    {
        return $request->validate([
            'question'          => ['required', 'string', 'max:1000'],
            'is_mandatory'      => ['nullable', 'boolean'],
            'doc_category_hint' => ['nullable', 'string', 'in:' . implode(',', self::DOC_CATEGORY_HINTS)],
        ]);
    }

    /**
     * Create a new Question record against the application and eager-load the author.
     *
     * @param  Application  $application  The parent application.
     * @param  array        $validated    Validated question payload.
     * @return Question                   The newly created and loaded question model.
     */
    private function createQuestion(Application $application, array $validated): Question
    {
        $question = $application->questions()->create([
            'asked_by'          => auth()->id(),
            'question'          => $validated['question'],
            'is_mandatory'      => $validated['is_mandatory'] ?? false,
            'doc_category_hint' => $validated['doc_category_hint'] ?? null,
            'status'            => 'pending',
            'asked_at'          => now(),
        ]);

        $question->load('askedBy');

        return $question;
    }

    // =========================================================================
    // Private Helpers — Notifications
    // =========================================================================

    /**
     * Notify the client of a newly asked question via email and, if available, SMS.
     *
     * Mandatory questions generate an "Action required" prefix in the SMS body
     * to indicate urgency. Notification failures are swallowed and logged so
     * that a delivery issue does not surface as an HTTP error to the admin.
     *
     * @param  Question     $question     The newly created question record.
     * @param  Application  $application  The parent application.
     * @return void
     */
    private function notifyClientOfQuestion(Question $question, Application $application): void
    {
        try {
            $application->user->notify(new QuestionAsked($question));

            if ($application->personalDetails?->mobile_phone) {
                $smsBody = $this->buildQuestionSmsBody($question, $application);

                app(MessagingService::class)->send(
                    $application->personalDetails->mobile_phone,
                    $smsBody,
                    $application
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to send question notification to client: ' . $e->getMessage(), [
                'question_id'    => $question->id,
                'application_id' => $application->id,
            ]);
        }
    }

    /**
     * Build the SMS body for a question notification.
     *
     * Mandatory questions include an "Action required" prefix to communicate
     * urgency. Optional questions use a softer informational phrasing.
     *
     * @param  Question     $question     The question being communicated.
     * @param  Application  $application  The parent application (used for the application number).
     * @return string                     The composed SMS message body.
     */
    private function buildQuestionSmsBody(Question $question, Application $application): string
    {
        $appNumber = $application->application_number;

        return $question->is_mandatory
            ? "Action required: A mandatory question has been asked on your application #{$appNumber}. Please log in to answer."
            : "A question has been asked on your application #{$appNumber}. Please log in to answer.";
    }

    // =========================================================================
    // Private Helpers — Formatting
    // =========================================================================

    /**
     * Map a Question model to the array shape used in JSON API responses.
     *
     * Answer-related fields (`answer`, `answered_at`, `answer_ip`) are included
     * as null stubs on creation so the front-end shape is consistent across
     * all question states.
     *
     * @param  Question  $question  The question record to format (must have `askedBy` loaded).
     * @return array                Associative array suitable for JSON serialisation.
     */
    private function formatQuestion(Question $question): array
    {
        return [
            'id'                => $question->id,
            'question'          => $question->question,
            'is_mandatory'      => $question->is_mandatory,
            'doc_category_hint' => $question->doc_category_hint,
            'status'            => $question->status,
            'asked_by'          => $question->askedBy->name,
            'asked_at'          => DateFormatter::datetime($question->asked_at),
            'answer'            => null,
            'answered_at'       => null,
            'answer_ip'         => null,
        ];
    }
}