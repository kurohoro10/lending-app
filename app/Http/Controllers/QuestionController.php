<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Question;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function answer(Request $request, Question $question)
    {
        $this->authorize('update', $question->application);

        if ($question->status !== 'pending') {
            return back()->with('error', 'This question has already been answered.');
        }

        $validated = $request->validate([
            'answer' => 'required|string',
        ]);

        $question->update([
            'answer' => $validated['answer'],
            'answered_by' => auth()->id(),
            'answered_at' => now(),
            'answer_ip' => $request->ip(),
            'status' => 'answered',
        ]);

        ActivityLog::logActivity(
            'answered',
            'Answered question',
            $question,
            null,
            $validated
        );

        // TODO: Send notification to assessor

        return back()->with('success', 'Question answered successfully.');
    }

    // Admin methods
    public function store(Request $request, Application $application)
    {
        $this->authorize('review', $application);

        $validated = $request->validate([
            'question' => 'required|string',
            'question_type' => 'required|in:structured,free_text,document_request,clarification',
            'options' => 'nullable|array',
            'is_mandatory' => 'boolean',
        ]);

        $question = $application->questions()->create([
            'asked_by' => auth()->id(),
            'question' => $validated['question'],
            'question_type' => $validated['question_type'],
            'options' => $validated['options'] ?? null,
            'is_mandatory' => $validated['is_mandatory'] ?? false,
        ]);

        ActivityLog::logActivity(
            'asked',
            'Asked question to client',
            $question,
            null,
            $validated
        );

        // TODO: Send notification to client

        return back()->with('success', 'Question sent to client successfully.');
    }

    public function destroy(Question $question)
    {
        $this->authorize('review', $question->application);

        $question->update(['status' => 'withdrawn']);

        ActivityLog::logActivity(
            'withdrawn',
            'Withdrew question',
            $question
        );

        return back()->with('success', 'Question withdrawn successfully.');
    }
}
