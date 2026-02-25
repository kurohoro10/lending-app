<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Question;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user       = auth()->user();
        $isAssessor = $user->hasRole('assessor');

        // Base query scoped to the assessor's own applications (or all for admins)
        $baseQuery = $isAssessor
            ? Application::where('assigned_to', $user->id)
            : Application::query();

        // Application statistics — scoped to what the current user can see
        $stats = [
            'total_applications'       => (clone $baseQuery)->count(),
            'draft'                    => (clone $baseQuery)->where('status', 'draft')->count(),
            'submitted'                => (clone $baseQuery)->where('status', 'submitted')->count(),
            'under_review'             => (clone $baseQuery)->where('status', 'under_review')->count(),
            'additional_info_required' => (clone $baseQuery)->where('status', 'additional_info_required')->count(),
            'approved'                 => (clone $baseQuery)->where('status', 'approved')->count(),
            'declined'                 => (clone $baseQuery)->where('status', 'declined')->count(),
        ];

        // Unread answered questions — scoped to the assessor's applications
        $answeredQuestionsQuery = Question::where('status', 'answered')->whereNull('read_at');
        if ($isAssessor) {
            $answeredQuestionsQuery->whereHas('application', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
        }
        $totalAnsweredQuestions = $answeredQuestionsQuery->count();

        // Recent applications — scoped the same way as the index page
        $recentApplications = (clone $baseQuery)
            ->with(['user', 'personalDetails', 'assignedTo'])
            ->withCount(['questions' => function ($q) {
                $q->where('status', 'answered')->whereNull('read_at');
            }])
            ->latest()
            ->limit(10)
            ->get();

        // Task statistics
        $taskStats = [
            'pending_tasks'   => Task::where('status', 'pending')->count(),
            'in_progress_tasks' => Task::where('status', 'in_progress')->count(),
            'overdue_tasks'   => Task::overdue()->count(),
            'completed_today' => Task::completed()->whereDate('completed_at', today())->count(),
        ];

        // My tasks (if assessor)
        $myTasks = [];
        if ($user->isAssessor()) {
            $myTasks = Task::with(['application.personalDetails'])
                ->where('assigned_to', $user->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->orderBy('due_date')
                ->limit(5)
                ->get();
        }

        // Applications by status (for chart) — scoped
        $applicationsByStatus = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Loan amount statistics — scoped
        $loanStats = [
            'total_requested' => (clone $baseQuery)->sum('loan_amount'),
            'total_approved'  => (clone $baseQuery)->where('status', 'approved')->sum('loan_amount'),
            'average_loan'    => (clone $baseQuery)->avg('loan_amount'),
        ];

        // Assessor workload (admin only)
        $assessorWorkload = [];
        if ($user->isAdmin()) {
            $assessorWorkload = User::role('assessor')
                ->withCount([
                    'assignedApplications as assigned_count' => function ($query) {
                        $query->whereNotIn('status', ['approved', 'declined', 'withdrawn']);
                    }
                ])
                ->get();
        }

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
}
