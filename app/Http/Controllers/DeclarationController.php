<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Declaration;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DeclarationController extends Controller
{
    public function store(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'declarations' => 'required|array',
            'declarations.*.declaration_type' => 'required|string',
            'declarations.*.is_agreed' => 'required|accepted',
            'declarations.*.signature' => 'nullable|string',
        ]);

        foreach ($validated['declarations'] as $declarationData) {
            // Get declaration text from defaults
            $declarationTexts = Declaration::getDefaultDeclarations();
            $declarationText = $declarationTexts[$declarationData['declaration_type']] ?? 'Declaration text not found';

            $declaration = $application->declarations()->create([
                'declaration_type' => $declarationData['declaration_type'],
                'declaration_text' => $declarationText,
                'is_agreed' => true,
                'agreed_at' => now(),
                'agreement_ip' => $request->ip(),
                'signature' => $declarationData['signature'] ?? null,
            ]);

            ActivityLog::logActivity(
                'agreed',
                "Agreed to {$declarationData['declaration_type']} declaration",
                $declaration,
                null,
                [
                    'type' => $declarationData['declaration_type'],
                    'ip' => $request->ip(),
                ]
            );
        }

        return back()->with('success', 'Declarations accepted successfully.');
    }

    public function index(Application $application)
    {
        $this->authorize('view', $application);

        $defaultDeclarations = Declaration::getDefaultDeclarations();
        $existingDeclarations = $application->declarations()->pluck('declaration_type')->toArray();

        $declarations = [];
        foreach ($defaultDeclarations as $type => $text) {
            $declarations[] = [
                'type' => $type,
                'text' => $text,
                'agreed' => in_array($type, $existingDeclarations),
            ];
        }

        return view('applications.declarations', compact('application', 'declarations'));
    }
}
