<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Communication;
use App\Models\ActivityLog;
use App\Services\MessagingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommunicationController extends Controller
{
    /**
     * Display a listing of communications for a specific application.
     */
    public function index(Request $request, Application $application): View
    {
        $query = $application->communications()->with('user');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $communications = $query->latest()->paginate(20);

        return view('admin.communications.index', compact('application', 'communications'));
    }

    /**
     * Display the specified communication record.
     */
    public function show(Communication $communication): View
    {
        $communication->load(['application', 'user']);

        return view('admin.communications.show', compact('communication'));
    }

    /**
     * Return application to client for amendments.
     */
    public function returnToClient(Request $request, Application $application)
    {
        $application->load('personalDetails', 'user');

        if (!in_array($application->status, ['submitted', 'under_review'])) {
            return back()->with('error', 'Only submitted or under review applications can be returned.');
        }

        $validated = $request->validate([
            'return_reason' => 'required|string|min:10|max:1000',
            'notify_sms'    => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $application->update([
                'status'        => 'additional_info_required',
                'return_reason' => $validated['return_reason'],
                'returned_at'   => now(),
                'returned_by'   => auth()->id(),
            ]);

            ActivityLog::logActivity(
                'returned_to_client',
                'Application returned to client: ' . $validated['return_reason'],
                $application
            );

            $application->comments()->create([
                'user_id'           => auth()->id(),
                'comment'           => 'Application returned for amendments: ' . $validated['return_reason'],
                'is_internal'       => false,
                'is_client_visible' => true,
                'commenter_ip'      => $request->ip(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to return application: ' . $e->getMessage());
            return back()->with('error', 'Failed to return application. Please try again.');
        }

        // Email notification — outside transaction
        try {
            $application->user->notify(
                new \App\Notifications\Application\ApplicationReturned($application)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send return email: ' . $e->getMessage());
        }

        // SMS notification — outside transaction
        if ($request->boolean('notify_sms') && $application->personalDetails?->mobile_phone) {
            try {
                app(MessagingService::class)->send(
                    $application->personalDetails->mobile_phone,
                    "Your loan application #{$application->application_number} has been returned for amendments. Reason: {$validated['return_reason']}. Please log in to update and resubmit.",
                    $application
                );
            } catch (\Exception $e) {
                Log::error('Failed to send return SMS: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Application returned to client successfully.');
    }
}
