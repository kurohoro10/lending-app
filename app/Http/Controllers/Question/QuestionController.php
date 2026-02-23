<?php

namespace App\Http\Controllers\Question;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Question;
use App\Models\User;
use App\Notifications\Admin\QuestionAnswered;
use App\Notifications\Application\QuestionAsked;
use App\Services\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{
    /**
     * Admin/assessor asks a question — route already protected by role middleware.
     */
    public function store(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'question'     => 'required|string|max:1000',
            'is_mandatory' => 'nullable|boolean',
        ]);

        $question = $application->questions()->create([
            'asked_by'     => auth()->id(),
            'question'     => $validated['question'],
            'is_mandatory' => $validated['is_mandatory'] ?? false,
            'status'       => 'pending',
            'asked_at'     => now(),
        ]);

        $question->load('askedBy');

        ActivityLog::logActivity('question_asked', 'Question asked to client', $application);

        $this->notifyClientOfQuestion($question, $application);

        return response()->json([
            'success'  => true,
            'message'  => 'Question sent to client.',
            'question' => $this->formatQuestion($question),
        ]);
    }

    /**
     * Client answers a question — ownership check remains since clients share the same auth guard.
     */
    public function answer(Request $request, Question $question): JsonResponse
    {
        if ($question->application->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'answer' => 'required|string|max:2000',
        ]);

        $question->update([
            'answer'      => $validated['answer'],
            'status'      => 'answered',
            'answered_at' => now(),
            'answered_by' => auth()->id(),
            'answer_ip'   => $request->ip(),
        ]);

        ActivityLog::logActivity('question_answered', 'Client answered question', $question->application);

        $this->notifyAdminsOfAnswer($question);

        return response()->json([
            'success'     => true,
            'message'     => 'Your answer has been submitted successfully.',
            'question_id' => $question->id,
            'answer'      => $question->answer,
            'answered_at' => $question->answered_at->format('d M Y H:i'),
            'answer_ip'   => $question->answer_ip,
        ]);
    }

    /**
     * Admin/assessor deletes a question — route already protected by role middleware.
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

    // ── Private helpers ───────────────────────────────────────────────────────

    private function notifyClientOfQuestion(Question $question, Application $application): void
    {
        try {
            $application->user->notify(new QuestionAsked($question));

            if ($application->personalDetails?->mobile_phone) {
                $smsMessage = $question->is_mandatory
                    ? "Action required: A mandatory question has been asked on your application #{$application->application_number}. Please log in to answer."
                    : "A question has been asked on your application #{$application->application_number}. Please log in to answer.";

                app(MessagingService::class)->send(
                    $application->personalDetails->mobile_phone,
                    $smsMessage,
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

    private function notifyAdminsOfAnswer(Question $question): void
    {
        try {
            User::role('admin')->get()->each(
                fn(User $admin) => $admin->notify(new QuestionAnswered($question))
            );
        } catch (\Exception $e) {
            Log::error('Failed to send question answered notification: ' . $e->getMessage(), [
                'question_id' => $question->id,
            ]);
        }
    }

    private function formatQuestion(Question $question): array
    {
        return [
            'id'           => $question->id,
            'question'     => $question->question,
            'is_mandatory' => $question->is_mandatory,
            'status'       => $question->status,
            'asked_by'     => $question->askedBy->name,
            'asked_at'     => $question->asked_at->format('d M Y H:i'),
            'answer'       => null,
            'answered_at'  => null,
            'answer_ip'    => null,
        ];
    }

    /**
     * Manually mark a question as read
     */
    public function markAsRead(Question $question): JsonResponse
    {
        if ($question->status !== 'answered') {
            return response()->json([
                'success' => false,
                'message' => 'Only answered questions can be marked as read.'
            ], 400);
        }

        $question->markAsRead(auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Question marked as read.',
            'read_at' => $question->read_at->format('d M Y H:i'),
            'read_by' => $question->readBy->name,
        ]);
    }
}
