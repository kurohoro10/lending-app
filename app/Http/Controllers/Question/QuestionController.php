<?php

namespace App\Http\Controllers\Question;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Application;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Admin asks a question
     */
    public function store(Request $request, Application $application)
    {
        if (!auth()->user()->hasAnyRole(['admin', 'assessor'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

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

        // Notify the client that a question has been asked
        try {
            $application->user->notify(new \App\Notifications\Application\QuestionAsked($question));

            // Send SMS if phone is available
            if ($application->personalDetails?->mobile_phone) {
                $smsMessage = $question->is_mandatory
                    ? "Action required: A mandatory question has been asked on your application #{$application->application_number}. Please log in to answer."
                    : "A question has been asked on your application #{$application->application_number}. Please log in to answer.";

                app(\App\Services\MessagingService::class)->send(
                    $application->personalDetails->mobile_phone,
                    $smsMessage,
                    $application
                );
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send question notification to client: ' . $e->getMessage());
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Question sent to client.',
            'question' => [
                'id'           => $question->id,
                'question'     => $question->question,
                'is_mandatory' => $question->is_mandatory,
                'status'       => $question->status,
                'asked_by'     => $question->askedBy->name,
                'asked_at'     => $question->asked_at->format('d M Y H:i'),
                'answer'       => null,
                'answered_at'  => null,
                'answer_ip'    => null,
            ],
        ]);
    }

    /**
     * Client answers a question
     */
    public function answer(Request $request, Question $question)
    {
        $application = $question->application;

        if ($application->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
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

        ActivityLog::logActivity('question_answered', 'Client answered question', $application);

        try {
            $admins = \App\Models\User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\Admin\QuestionAnswered($question));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send question answered notification: ' . $e->getMessage());
        }

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
     * Admin/Assessor deletes a question
     */
    public function destroy(Question $question)
    {
        $application = $question->application;

        if (!auth()->user()->hasAnyRole(['admin', 'assessor'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $question->delete();

        ActivityLog::logActivity('question_deleted', 'Question deleted', $application);

        return response()->json([
            'success' => true,
            'message' => 'Question deleted.',
        ]);
    }
}
