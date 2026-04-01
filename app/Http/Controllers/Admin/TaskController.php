<?php

/**
 * @file    app/Http/Controllers/Admin/TaskController.php
 * @package App\Http\Controllers\Admin
 *
 * Manages the full lifecycle of tasks associated with loan applications
 * within the commercial loan application system.
 *
 * Responsibilities:
 *  - Listing tasks with role-scoped visibility and optional filters
 *  - Creating tasks against a specific application and notifying the assignee
 *  - Updating task metadata and tracking field-level changes for audit
 *  - Marking tasks as complete with optional completion notes
 *  - Soft or hard deleting tasks and logging the activity
 *
 * Role behaviour:
 *  - `admin`    — sees all tasks; may filter by assignee
 *  - `assessor` — scoped to tasks assigned to themselves
 *
 * Valid task types:
 *  id_check | living_expense_check | declaration_verification |
 *  credit_check | document_review | employment_verification | other
 *
 * Valid priorities:  low | medium | high | urgent
 * Valid statuses:    pending | in_progress | completed | cancelled
 *
 * Pending integration:
 *  - Assigned-user notification on task creation (see TODO in store())
 *
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Valid task type identifiers accepted on creation.
     *
     * @var string[]
     */
    private const TASK_TYPES = [
        'id_check',
        'living_expense_check',
        'declaration_verification',
        'credit_check',
        'document_review',
        'employment_verification',
        'other',
    ];

    /**
     * Valid task priority levels.
     *
     * @var string[]
     */
    private const PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    /**
     * Valid task status values.
     *
     * @var string[]
     */
    private const STATUSES = ['pending', 'in_progress', 'completed', 'cancelled'];

    // =========================================================================
    // Listing
    // =========================================================================

    /**
     * Display a paginated listing of tasks with optional filters.
     *
     * Assessors see only tasks assigned to themselves. Admins see all tasks
     * and may additionally filter by assignee. Supports optional filtering
     * by status, task type, and assigned user.
     *
     * @param  Request  $request  Incoming HTTP request; supports `status`, `task_type`,
     *                            and `assigned_to` query parameters.
     * @return View               The `admin.tasks.index` view.
     *
     * @queryParam string status      Filter by task status.
     * @queryParam string task_type   Filter by task type identifier.
     * @queryParam int    assigned_to Filter by assigned user ID.
     */
    public function index(Request $request): View
    {
        $query = $this->buildIndexQuery($request);

        $tasks     = $query->latest()->paginate(20);
        $assessors = User::role(['admin', 'assessor'])->get();

        return view('admin.tasks.index', compact('tasks', 'assessors'));
    }

    // =========================================================================
    // Creation
    // =========================================================================

    /**
     * Create a new task against a specific application.
     *
     * Validates the payload, persists the task with the current admin as creator,
     * and logs the activity. Assignee notification is stubbed pending integration.
     *
     * @param  Request      $request      Incoming HTTP request with task payload.
     * @param  Application  $application  The bound application model instance.
     * @return RedirectResponse           Redirect back with success flash message.
     *
     * @bodyParam string task_type   required  Task type — one of the TASK_TYPES values.
     * @bodyParam string title       required  Task title (max 255 chars).
     * @bodyParam string description nullable  Optional task description.
     * @bodyParam string priority    required  Priority level — low | medium | high | urgent.
     * @bodyParam int    assigned_to required  ID of the user to assign the task to.
     * @bodyParam date   due_date    nullable  Optional due date (must be after today).
     */
    public function store(Request $request, Application $application): RedirectResponse
    {
        $validated = $this->validateTaskCreationPayload($request);

        $task = $this->createTask($application, $validated);

        ActivityLog::logActivity(
            'created',
            "Created task: {$validated['title']}",
            $task,
            null,
            $validated
        );

        // TODO: Send notification to the assigned user when integration is ready.

        return back()->with('success', 'Task created successfully.');
    }

    // =========================================================================
    // Update
    // =========================================================================

    /**
     * Update an existing task's metadata.
     *
     * Captures the previous values of auditable fields before applying changes
     * so the activity log records a meaningful before/after diff.
     *
     * @param  Request  $request  Incoming HTTP request with updated task fields.
     * @param  Task     $task     The bound task model instance.
     * @return RedirectResponse   Redirect back with success flash message.
     *
     * @bodyParam string title       required  Updated task title (max 255 chars).
     * @bodyParam string description nullable  Updated task description.
     * @bodyParam string priority    required  Updated priority — low | medium | high | urgent.
     * @bodyParam int    assigned_to required  Updated assignee user ID.
     * @bodyParam date   due_date    nullable  Updated due date.
     * @bodyParam string status      required  Updated status — pending | in_progress | completed | cancelled.
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $this->validateTaskUpdatePayload($request);

        $oldValues = $this->captureAuditSnapshot($task);

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

    // =========================================================================
    // Completion
    // =========================================================================

    /**
     * Mark a task as complete with optional completion notes.
     *
     * Delegates the status transition and timestamp to the `Task::complete()`
     * model method, then logs the activity against the task record.
     *
     * @param  Request  $request  Incoming HTTP request with optional completion notes.
     * @param  Task     $task     The bound task model instance.
     * @return RedirectResponse   Redirect back with success flash message.
     *
     * @bodyParam string completion_notes nullable  Optional notes recorded on completion.
     */
    public function complete(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'completion_notes' => ['nullable', 'string'],
        ]);

        $task->complete($validated['completion_notes'] ?? null);

        ActivityLog::logActivity(
            'completed',
            "Completed task: {$task->title}",
            $task
        );

        return back()->with('success', 'Task completed successfully.');
    }

    // =========================================================================
    // Deletion
    // =========================================================================

    /**
     * Delete a task and log the activity against its parent application.
     *
     * Captures the task title before deletion so it remains available for
     * the activity log message after the record is removed.
     *
     * @param  Task  $task  The bound task model instance.
     * @return RedirectResponse  Redirect back with success flash message.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $title       = $task->title;
        $application = $task->application;

        $task->delete();

        ActivityLog::logActivity(
            'deleted',
            "Deleted task: {$title}",
            $application
        );

        return back()->with('success', 'Task deleted successfully.');
    }

    // =========================================================================
    // Private Helpers — Index
    // =========================================================================

    /**
     * Build the base Eloquent query for the tasks index.
     *
     * Applies role-scoping, status filter, task type filter, and assignee
     * filter based on the authenticated user's role and request parameters.
     * Eager-loads relationships required by the index view.
     *
     * @param  Request  $request  The incoming index request.
     * @return Builder            Configured query builder for Task.
     */
    private function buildIndexQuery(Request $request): Builder
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

        if (auth()->user()->isAssessor() && ! auth()->user()->isAdmin()) {
            $query->where('assigned_to', auth()->id());
        }

        return $query;
    }

    // =========================================================================
    // Private Helpers — Validation
    // =========================================================================

    /**
     * Validate the task creation request payload.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array              Validated task creation fields.
     */
    private function validateTaskCreationPayload(Request $request): array
    {
        return $request->validate([
            'task_type'   => ['required', 'in:' . implode(',', self::TASK_TYPES)],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority'    => ['required', 'in:' . implode(',', self::PRIORITIES)],
            'assigned_to' => ['required', 'exists:users,id'],
            'due_date'    => ['nullable', 'date', 'after:today'],
        ]);
    }

    /**
     * Validate the task update request payload.
     *
     * @param  Request  $request  The incoming HTTP request.
     * @return array              Validated task update fields.
     */
    private function validateTaskUpdatePayload(Request $request): array
    {
        return $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority'    => ['required', 'in:' . implode(',', self::PRIORITIES)],
            'assigned_to' => ['required', 'exists:users,id'],
            'due_date'    => ['nullable', 'date'],
            'status'      => ['required', 'in:' . implode(',', self::STATUSES)],
        ]);
    }

    // =========================================================================
    // Private Helpers — Persistence
    // =========================================================================

    /**
     * Create a new Task record against the application.
     *
     * @param  Application  $application  The parent application.
     * @param  array        $validated    Validated task creation payload.
     * @return Task                       The newly created task model.
     */
    private function createTask(Application $application, array $validated): Task
    {
        return $application->tasks()->create([
            'created_by'  => auth()->id(),
            'task_type'   => $validated['task_type'],
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority'    => $validated['priority'],
            'assigned_to' => $validated['assigned_to'],
            'due_date'    => $validated['due_date'] ?? null,
        ]);
    }

    /**
     * Capture a before-snapshot of auditable task fields for the activity log.
     *
     * @param  Task  $task  The task record before update.
     * @return array        Associative array of previous field values.
     */
    private function captureAuditSnapshot(Task $task): array
    {
        return $task->only(['title', 'priority', 'assigned_to', 'status']);
    }
}