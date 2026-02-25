<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Declaration;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeclarationController extends Controller
{
    /**
     * Store declarations from the client
     */
    public function store(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'declarations'                         => 'required|array',
            'declarations.*.declaration_type'      => 'required|string',
            'declarations.*.is_agreed'             => 'required|accepted',
            'declarations.*.signature_data'        => 'nullable|string', // ✅ FIX: signature_data not signature
            'declarations.*.signature_type'        => 'nullable|string', // ✅ ADD: for typed/drawn/uploaded
            'declarations.*.signatory_position'    => 'nullable|string', // ✅ ADD: optional position
        ]);

        foreach ($validated['declarations'] as $declarationData) {
            // Get declaration text from defaults
            $declarationTexts = Declaration::getDefaultDeclarations();
            $declarationText = $declarationTexts[$declarationData['declaration_type']]
                ?? 'Declaration text not found';

            $application->declarations()->create([
                'declaration_type'   => $declarationData['declaration_type'],
                'declaration_text'   => $declarationText,
                'is_agreed'          => true,
                'agreed_at'          => now(),
                'agreement_ip'       => $request->ip(),
                'signature_data'     => $declarationData['signature_data'] ?? null, // ✅ FIX
                'signature_type'     => $declarationData['signature_type'] ?? null, // ✅ ADD
                'signatory_name'     => auth()->user()->name, // ✅ ADD: who signed
                'signatory_position' => $declarationData['signatory_position'] ?? null, // ✅ ADD
                'signature_timestamp'=> now(), // ✅ ADD: when signed
            ]);

            // ✅ FIX: Log against Application not Declaration
            ActivityLog::logActivity(
                'declaration_agreed',
                "Client agreed to {$declarationData['declaration_type']} declaration",
                $application, // ✅ FIX: Pass application not declaration
                null,
                [
                    'type' => $declarationData['declaration_type'],
                    'ip'   => $request->ip(),
                ]
            );
        }

        return back()->with('success', 'Declarations accepted successfully.');
    }

    /**
     * Show all declarations for review
     */
    public function index(Application $application): View
    {
        $this->authorize('view', $application);

        $defaultDeclarations = Declaration::getDefaultDeclarations();
        $existingDeclarations = $application->declarations()
            ->pluck('declaration_type')
            ->toArray();

        $declarations = [];
        foreach ($defaultDeclarations as $type => $text) {
            $declarations[] = [
                'type'   => $type,
                'text'   => $text,
                'agreed' => in_array($type, $existingDeclarations),
            ];
        }

        return view('applications.declarations', compact('application', 'declarations'));
    }
}
