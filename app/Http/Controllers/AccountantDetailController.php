<?php
// app/Http/Controllers/AccountantDetailController.php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountantDetailController extends Controller
{
    public function store(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'accountant_name'       => 'required|string|max:255',
            'accountant_phone'      => 'nullable|string|max:20',
            'years_with_accountant' => 'nullable|integer|min:0|max:100',
        ]);

        $validated['application_id'] = $application->id;

        $accountant = $application->accountantDetail
            ? tap($application->accountantDetail)->update($validated)
            : $application->accountantDetail()->create($validated);

        return response()->json([
            'success'    => true,
            'message'    => 'Accountant details saved.',
            'accountant' => [
                'accountant_name'       => $accountant->accountant_name,
                'accountant_phone'      => $accountant->accountant_phone,
                'years_with_accountant' => $accountant->years_with_accountant,
            ],
        ]);
    }
}
