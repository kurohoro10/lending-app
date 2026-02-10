<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Task;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['application.personalDetails', 'assignedTo', 'createdBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('task_type')) {
            $query->where('task_type', $request->task_type);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if (auth()->user()->isAssessor() && !auth()->user()->isAdmin()) {
            $query->where('assigned_to', auth()->id());
        }

        $tasks = $query->latest()->paginate(20);
        $assessors = User::role(['admin', 'assessor'])->get();

        return view('admin.tasks.index', compact('tasks', 'assessors'));
    }

    public function store(Request $request, Application $application)
    {
        $validated = $request->validate([
            'task_type'   => 'required|in:id_check,living_expense_check,declaration_verification,credit_check,document_review,employment_verification,other',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'required|exists:users,id',
            'due_date'    => 'nullable|date|after:today',
        ]);

        $task = $application->tasks()->create([
            'created_by'  => auth()->id(),
            'task_type'   => $validated['task_type'],
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority'    => $validated['priority'],
            'assigned_to' => $validated['assigned_to'],
            'due_date'    => $validated['due_date'] ?? null,
        ]);

        ActivityLog::logActivity(
            'created',
            "Created task: {$validated['title']}",
            $task,
            null,
            $validated
        );

        // TODO: Send notification to assigned user

        return back()->with('success', 'Task created successfully.');
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority'    => 'required|in:low,medium,high,urgent',
            'assigned_to' => 'required|exists:users,id',
            'due_date'    => 'nullable|date',
            'status'      => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $oldValues = $task->only(['title', 'priority', 'assigned_to', 'status']);
        $task->update($validated);

        ActivityLog::logActivity(
            'updated',
            "Updated task: {$validated['title']}",
            $task,
            $oldValues,
            $validated
        );

        return back()->with('success', 'Task updated successfully.');
    }

    public function complete(Request $request, Task $task)
    {
        $validated = $request->validate([
            'completion_notes' => 'nullable|string',
        ]);

        $task->complete($validated['completion_notes'] ?? null);

        ActivityLog::logActivity(
            'completed',
            "Completed task: {$task->title}",
            $task
        );

        return back()->with('success', 'Task completed successfully.');
    }

    public function destroy(Task $task)
    {
        $title = $task->title;
        $task->delete();

        ActivityLog::logActivity(
            'deleted',
            "Deleted task: {$title}",
            $task->application
        );

        return back()->with('success', 'Task deleted successfully.');
    }
}
