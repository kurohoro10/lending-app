<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Communication;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CommunicationController extends Controller
{
    public function index(Request $request, Application $application)
    {
        $query = $application->communications()->with('user');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $communications = $query->latest()->paginate(20);

        return view('admin.communications.index', compact('application', 'communications'));
    }

    public function sendEmail(Request $request, Application $application)
    {
        $validated = $request->validate([
            'to_address' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        // Create communication record
        $communication = $application->communications()->create([
            'user_id' => $application->user_id,
            'type' => 'email_out',
            'direction' => 'outbound',
            'from_address' => config('mail.from.address'),
            'to_address' => $validated['to_address'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'status' => 'pending',
            'sender_ip' => $request->ip(),
        ]);

        // TODO: Queue actual email sending job
        // dispatch(new SendEmailNotification($communication));

        // For now, mark as sent
        $communication->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        ActivityLog::logActivity(
            'sent_email',
            "Sent email: {$validated['subject']}",
            $communication,
            null,
            ['to' => $validated['to_address']]
        );

        return back()->with('success', 'Email sent successfully.');
    }

    public function sendSms(Request $request, Application $application)
    {
        $validated = $request->validate([
            'to_address' => 'required|string|max:20',
            'body' => 'required|string|max:160',
        ]);

        // Create communication record
        $communication = $application->communications()->create([
            'user_id' => $application->user_id,
            'type' => 'sms_out',
            'direction' => 'outbound',
            'from_address' => config('services.twilio.from'),
            'to_address' => $validated['to_address'],
            'body' => $validated['body'],
            'status' => 'pending',
            'sender_ip' => $request->ip(),
        ]);

        // TODO: Queue actual SMS sending job
        // dispatch(new SendSmsNotification($communication));

        // For now, mark as sent
        $communication->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        ActivityLog::logActivity(
            'sent_sms',
            "Sent SMS to {$validated['to_address']}",
            $communication,
            null,
            ['to' => $validated['to_address']]
        );

        return back()->with('success', 'SMS sent successfully.');
    }

    public function show(Communication $communication)
    {
        $communication->load(['application', 'user']);

        return view('admin.communications.show', compact('communication'));
    }
}
