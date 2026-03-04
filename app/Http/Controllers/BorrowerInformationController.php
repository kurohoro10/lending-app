<?php
// app/Http/Controllers/BorrowerInformationController.php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BorrowerInformation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BorrowerInformationController extends Controller
{
    public function store(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'borrower_name'      => 'required|string|max:255',
            'borrower_type'      => 'required|in:individual,company,trust,other',
            'abn'                => 'nullable|string|digits:11',
            'nature_of_business' => 'nullable|string|max:255',
            'years_in_business'  => 'nullable|integer|min:0|max:200',
        ]);

        $borrower = BorrowerInformation::updateOrCreate(
            ['application_id' => $application->id],
            $validated
        );

        return response()->json([
            'success'  => true,
            'message'  => 'Borrower information saved.',
            'borrower' => $borrower,
        ]);
    }
}
