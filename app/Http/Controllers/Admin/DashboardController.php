<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Application statistics
        $stats = [
            'total_applications' => Application::count(),
            'draft' => Application::where('status', 'draft')->count(),
            'submitted' => Application::where('status', 'submitted')->count(),
            'under_review' => Application::where('status', 'under_review')->count(),
            'additional_info_required' => Application::where('status', 'additional_info_required')->count(),
            'approved' => Application::where('status', 'approved')->count(),
            'declined' => Application::where('status', 'declined')->count(),
        ];

        // Recent applications
        $recentApplications = Application::with(['user', 'personalDetails', 'assignedTo'])
            ->latest()
            ->limit(10)
            ->get();

        // Task statistics
        $taskStats = [
            'pending_tasks' => Task::where('status', 'pending')->count(),
            'in_progress_tasks' => Task::where('status', 'in_progress')->count(),
            'overdue_tasks' => Task::overdue()->count(),
            'completed_today' => Task::completed()
                ->whereDate('completed_at', today())
                ->count(),
        ];

        // My tasks (if assessor)
        $myTasks = [];
        if (auth()->user()->isAssessor()) {
            $myTasks = Task::with(['application.personalDetails'])
                ->where('assigned_to', auth()->id())
                ->whereIn('status', ['pending', 'in_progress'])
                ->orderBy('due_date')
                ->limit(5)
                ->get();
        }

        // Applications by status (for chart)
        $applicationsByStatus = Application::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Loan amount statistics
        $loanStats = [
            'total_requested' => Application::sum('loan_amount'),
            'total_approved' => Application::where('status', 'approved')->sum('loan_amount'),
            'average_loan' => Application::avg('loan_amount'),
        ];

        // Assessor workload
        $assessorWorkload = [];
        if (auth()->user()->isAdmin()) {
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
            'recentApplications',
            'taskStats',
            'myTasks',
            'applicationsByStatus',
            'loanStats',
            'assessorWorkload'
        ));
    }
}
