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
     * Admin asks a question (already exists in admin routes)
     */
    public function store(Request $request, Application $application)
    {
        // $this->authorize('update', $application);
        if (!auth()->user()->hasAnyRole(['admin', 'assessor'])) {
            abort(403);
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

        ActivityLog::logActivity(
            'question_asked',
            'Question asked to client',
            $application
        );

        return back()->with('success', 'Question sent to client.');
    }

    /**
     * Client answers a question
     */
    public function answer(Request $request, Question $question)
    {
        // Check if user owns the application
        $application = $question->application;

        if ($application->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
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

        ActivityLog::logActivity(
            'question_answered',
            'Client answered question',
            $application
        );

        // Send notification to admin
        try {
            $admins = \App\Models\User::role('admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\Admin\QuestionAnswered($question));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send question answered notification: ' . $e->getMessage());
        }

        return back()->with('success', 'Your answer has been submitted successfully.');
    }

    /**
     * Admin/Assessor deletes a question
     */
    public function destroy(Question $question)
    {
        $application = $question->application;
        $this->authorize('update', $application);

        $question->delete();

        ActivityLog::logActivity(
            'question_deleted',
            'Question deleted',
            $application
        );

        return back()->with('success', 'Question deleted.');
    }
}
