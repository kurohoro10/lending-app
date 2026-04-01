<?php

/**
 * @file    app/Http/Controllers/Admin/DashboardController.php
 * @package App\Http\Controllers\Admin
 *
 * Compiles and serves the admin dashboard data for the commercial loan
 * application system.
 *
 * Responsibilities:
 *  - Aggregating role-scoped application statistics
 *  - Counting unread answered questions visible to the current user
 *  - Fetching recent applications with unread question badge counts
 *  - Summarising task statistics and the current user's open task list
 *  - Computing loan amount aggregates (total requested, approved, average)
 *  - Building assessor workload data (admin only)
 *  - Preparing status distribution data for the dashboard chart
 *
 * Role behaviour:
 *  - `admin`    — sees all applications, all tasks, and assessor workload
 *  - `assessor` — scoped to their own assigned applications and tasks
 *
 * @author  Your Name <you@example.com>
 * @since   1.0.0
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Question;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // =========================================================================
    // Entry Point
    // =========================================================================

    /**
     * Render the admin dashboard view with all aggregated data.
     *
     * Builds a role-scoped base query used across multiple stat blocks,
     * then delegates each data group to a focused private method before
     * passing all results to the view.
     *
     * @return View  The `admin.dashboard` view.
     */
    public function index(): View
    {
        $user       = auth()->user();
        $isAssessor = $user->hasRole('assessor');
        $baseQuery  = $this->buildBaseQuery($user->id, $isAssessor);

        $stats                = $this->buildApplicationStats($baseQuery, $user);
        $totalAnsweredQuestions = $this->countUnreadAnsweredQuestions($user->id, $isAssessor);
        $recentApplications   = $this->getRecentApplications($baseQuery);
        $taskStats            = $this->buildTaskStats();
        $myTasks              = $this->getMyTasks($user);
        $applicationsByStatus = $this->getApplicationsByStatus($baseQuery);
        $loanStats            = $this->buildLoanStats($baseQuery);
        $assessorWorkload     = $this->getAssessorWorkload($user);

        return view('admin.dashboard', compact(
            'stats',
            'totalAnsweredQuestions',
            'recentApplications',
            'taskStats',
            'myTasks',
            'applicationsByStatus',
            'loanStats',
            'assessorWorkload'
        ));
    }

    // =========================================================================
    // Private Helpers — Query Scoping
    // =========================================================================

    /**
     * Build the role-scoped base Eloquent query for applications.
     *
     * Assessors see only applications assigned to themselves. Admins receive
     * an unscoped query covering all applications.
     *
     * @param  int   $userId      The authenticated user's ID.
     * @param  bool  $isAssessor  Whether the current user has the assessor role.
     * @return Builder            Configured query builder for Application.
     */
    private function buildBaseQuery(int $userId, bool $isAssessor): Builder
    {
        return $isAssessor
            ? Application::where('assigned_to', $userId)
            : Application::query();
    }

    // =========================================================================
    // Private Helpers — Application Statistics
    // =========================================================================

    /**
     * Build the application status statistics block.
     *
     * Counts applications by each status, scoped to the current user's
     * visibility. Also appends task counts for the current user and, for
     * admins, system-wide task totals.
     *
     * @param  Builder  $baseQuery  The role-scoped application query.
     * @param  mixed    $user       The authenticated user model.
     * @return array                Associative array of statistic labels to counts.
     */
    private function buildApplicationStats(Builder $baseQuery, mixed $user): array
    {
        $stats = [
            'total_applications'       => (clone $baseQuery)->count(),
            'draft'                    => (clone $baseQuery)->where('status', 'draft')->count(),
            'submitted'                => (clone $baseQuery)->where('status', 'submitted')->count(),
            'under_review'             => (clone $baseQuery)->where('status', 'under_review')->count(),
            'additional_info_required' => (clone $baseQuery)->where('status', 'additional_info_required')->count(),
            'approved'                 => (clone $baseQuery)->where('status', 'approved')->count(),
            'declined'                 => (clone $baseQuery)->where('status', 'declined')->count(),
        ];

        $stats['my_tasks'] = $user->isAssessor()
            ? Task::where('assigned_to', $user->id)->whereNull('completed_at')->count()
            : 0;

        $stats['all_tasks'] = $user->hasRole('admin')
            ? Task::whereNull('completed_at')->count()
            : 0;

        $stats['overdue_tasks'] = $user->hasRole('admin')
            ? Task::whereNull('completed_at')->where('due_date', '<', now())->count()
            : Task::where('assigned_to', $user->id)->whereNull('completed_at')->where('due_date', '<', now())->count();

        return $stats;
    }

    // =========================================================================
    // Private Helpers — Questions
    // =========================================================================

    /**
     * Count unread answered questions visible to the current user.
     *
     * Assessors see only questions on applications assigned to themselves.
     * Admins see counts across all applications.
     *
     * @param  int   $userId      The authenticated user's ID.
     * @param  bool  $isAssessor  Whether the current user has the assessor role.
     * @return int                Number of answered but unread questions.
     */
    private function countUnreadAnsweredQuestions(int $userId, bool $isAssessor): int
    {
        $query = Question::where('status', 'answered')->whereNull('read_at');

        if ($isAssessor) {
            $query->whereHas('application', function ($q) use ($userId) {
                $q->where('assigned_to', $userId);
            });
        }

        return $query->count();
    }

    // =========================================================================
    // Private Helpers — Recent Applications
    // =========================================================================

    /**
     * Fetch the ten most recent applications with unread question badge counts.
     *
     * Applies the same role-scope as the applications index page, eager-loads
     * the owner, personal details, and assigned assessor, and annotates each
     * record with a count of answered but unread questions.
     *
     * @param  Builder  $baseQuery  The role-scoped application query.
     * @return \Illuminate\Support\Collection  Collection of up to 10 Application models.
     */
    private function getRecentApplications(Builder $baseQuery): \Illuminate\Support\Collection
    {
        return (clone $baseQuery)
            ->with(['user', 'personalDetails', 'assignedTo'])
            ->withCount(['questions' => function ($q) {
                $q->where('status', 'answered')->whereNull('read_at');
            }])
            ->latest()
            ->limit(10)
            ->get();
    }

    // =========================================================================
    // Private Helpers — Task Statistics
    // =========================================================================

    /**
     * Build the system-wide task statistics block.
     *
     * These counts are not role-scoped — all roles see the global task
     * summary on the dashboard. Per-user task data is handled separately
     * by getMyTasks().
     *
     * @return array  Associative array of task statistic labels to counts.
     */
    private function buildTaskStats(): array
    {
        return [
            'pending_tasks'     => Task::where('status', 'pending')->count(),
            'in_progress_tasks' => Task::where('status', 'in_progress')->count(),
            'overdue_tasks'     => Task::overdue()->count(),
            'completed_today'   => Task::completed()->whereDate('completed_at', today())->count(),
        ];
    }

    /**
     * Fetch the assessor's own open tasks, ordered by due date.
     *
     * Returns an empty array for admins, who view task data through the
     * system-wide task statistics block instead.
     *
     * @param  mixed  $user  The authenticated user model.
     * @return \Illuminate\Support\Collection|array  Up to 5 open Task models, or empty array.
     */
    private function getMyTasks(mixed $user): \Illuminate\Support\Collection|array
    {
        if (! $user->isAssessor()) {
            return [];
        }

        return Task::with(['application.personalDetails'])
            ->where('assigned_to', $user->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderBy('due_date')
            ->limit(5)
            ->get();
    }

    // =========================================================================
    // Private Helpers — Chart Data
    // =========================================================================

    /**
     * Retrieve application counts grouped by status for the dashboard chart.
     *
     * Results are scoped to the current user's visible applications and
     * returned as a flat `status → count` collection suitable for charting.
     *
     * @param  Builder  $baseQuery  The role-scoped application query.
     * @return \Illuminate\Support\Collection  Collection keyed by status with count values.
     */
    private function getApplicationsByStatus(Builder $baseQuery): \Illuminate\Support\Collection
    {
        return (clone $baseQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
    }

    // =========================================================================
    // Private Helpers — Loan Statistics
    // =========================================================================

    /**
     * Build the loan amount aggregates block.
     *
     * Computes total requested, total approved, and average loan amounts
     * scoped to the current user's visible applications.
     *
     * @param  Builder  $baseQuery  The role-scoped application query.
     * @return array                Associative array of loan aggregate labels to values.
     */
    private function buildLoanStats(Builder $baseQuery): array
    {
        return [
            'total_requested' => (clone $baseQuery)->sum('loan_amount'),
            'total_approved'  => (clone $baseQuery)->where('status', 'approved')->sum('loan_amount'),
            'average_loan'    => (clone $baseQuery)->avg('loan_amount'),
        ];
    }

    // =========================================================================
    // Private Helpers — Assessor Workload
    // =========================================================================

    /**
     * Retrieve assessor workload data for the admin overview panel.
     *
     * Returns an empty array for non-admin users. For admins, fetches all
     * assessor users annotated with a count of their active (non-terminal)
     * assigned applications.
     *
     * Terminal statuses excluded from the count: `approved`, `declined`, `withdrawn`.
     *
     * @param  mixed  $user  The authenticated user model.
     * @return \Illuminate\Support\Collection|array  Collection of User models with `assigned_count`,
     *                                               or an empty array for non-admins.
     */
    private function getAssessorWorkload(mixed $user): \Illuminate\Support\Collection|array
    {
        if (! $user->isAdmin()) {
            return [];
        }

        return User::role('assessor')
            ->withCount([
                'assignedApplications as assigned_count' => function ($query) {
                    $query->whereNotIn('status', ['approved', 'declined', 'withdrawn']);
                },
            ])
            ->get();
    }
}