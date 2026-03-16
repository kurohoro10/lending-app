<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Question;
use App\Models\User;
use App\Notifications\Admin\QuestionAnswered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Helpers\DateFormatter;

class QuestionController extends Controller
{
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
            'answered_at' => DateFormatter::datetime($question->answered_at),
            'answer_ip'   => $question->answer_ip,
        ]);
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
}
