<?php
// app/Http/Controllers/BorrowerDirectorController.php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BorrowerDirector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BorrowerDirectorController extends Controller
{
    public function store(Request $request, Application $application): JsonResponse
    {
        $this->authorizeDirectorSection($application);

        $validated = $request->validate([
            'full_name'            => 'required|string|max:255',
            'email'                => 'nullable|email|max:255',
            'phone'                => 'nullable|string|max:20',
            'date_of_birth'        => 'nullable|date|before:today',
            'ownership_percentage' => 'nullable|numeric|min:0|max:100',
            'is_guarantor'         => 'nullable|boolean',
        ]);

        $validated['application_id'] = $application->id;
        $validated['is_guarantor']   = $request->boolean('is_guarantor');

        $director = BorrowerDirector::create($validated);

        return response()->json([
            'success'  => true,
            'message'  => 'Director added successfully.',
            'director' => $this->formatDirector($director),
        ]);
    }

    public function update(Request $request, Application $application, BorrowerDirector $director): JsonResponse
    {
        $this->authorizeDirectorSection($application);
        abort_if($director->application_id !== $application->id, 403);

        $validated = $request->validate([
            'full_name'            => 'required|string|max:255',
            'email'                => 'nullable|email|max:255',
            'phone'                => 'nullable|string|max:20',
            'date_of_birth'        => 'nullable|date|before:today',
            'ownership_percentage' => 'nullable|numeric|min:0|max:100',
            'is_guarantor'         => 'nullable|boolean',
        ]);

        $validated['is_guarantor'] = $request->boolean('is_guarantor');
        $director->update($validated);

        return response()->json([
            'success'  => true,
            'message'  => 'Director updated successfully.',
            'director' => $this->formatDirector($director->fresh()),
        ]);
    }

    public function destroy(Application $application, BorrowerDirector $director): JsonResponse
    {
        $this->authorizeDirectorSection($application);
        abort_if($director->application_id !== $application->id, 403);

        $director->delete();

        return response()->json([
            'success' => true,
            'message' => 'Director removed.',
        ]);
    }

    private function authorizeDirectorSection(Application $application): void
    {
        $type = $application->borrowerInformation?->borrower_type;
        abort_if(!in_array($type, ['company', 'trust']), 422, 'Director section only applies to Company or Trust borrowers.');
    }

    private function formatDirector(BorrowerDirector $d): array
    {
        return [
            'id'                   => $d->id,
            'full_name'            => $d->full_name,
            'email'                => $d->email,
            'phone'                => $d->phone,
            'date_of_birth'        => $d->date_of_birth?->format('Y-m-d'),
            'ownership_percentage' => $d->ownership_percentage,
            'is_guarantor'         => $d->is_guarantor,
        ];
    }
}
