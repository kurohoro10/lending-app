<?php

namespace App\Http\Controllers;

use App\Models\Communication;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskResponseController extends Controller
{
    public function show(Request $request, Task $task)
    {
        // Validate token
        abort_if(
            !$request->has('token') || $request->get('token') !== $task->response_token,
            403,
            'Invalid or expired response link.'
        );

        abort_if($task->client_response !== null, 410, 'You have already responded to this task.');

        return view('tasks.respond', compact('task'));
    }

    public function store(Request $request, Task $task)
    {
        abort_if(
            !$request->has('token') || $request->get('token') !== $task->response_token,
            403,
            'Invalid or expired response link.'
        );

        abort_if($task->client_response !== null, 410, 'You have already responded to this task.');

        $validated = $request->validate([
            'response' => 'required|string|max:2000',
        ]);

        DB::transaction(function () use ($task, $validated, $request) {
            $task->recordClientResponse($validated['response']);

            // Log the client's response as an inbound email so it lands in the same
            // conversation thread/timeline as the outbound "task sent" email.
            Communication::create([
                'application_id' => $task->application_id,
                'user_id'        => $task->application->user->id,
                'type'           => 'email_in',
                'direction'      => 'inbound',
                'from_address'   => $task->application->user->email,
                'to_address'     => config('mail.from.address'),
                'subject'        => 'Re: Task: ' . $task->title,
                'body'           => $validated['response'],
                'status'         => 'delivered',
                'sent_at'        => now(),
                'sender_ip'      => $request->ip(),
            ]);
        });

        return view('tasks.respond-success', compact('task'));
    }
}
