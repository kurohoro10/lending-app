<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ResidentialAddress;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ResidentialAddressController extends Controller
{
    public function store(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'address_type' => 'required|in:current,previous_1,previous_2,previous_3',
            'street_address' => 'required|string|max:255',
            'suburb' => 'required|string|max:100',
            'state' => 'required|string|max:50',
            'postcode' => 'required|string|max:10',
            'country' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'residential_status' => 'nullable|in:own,rent,boarding,living_with_parents',
        ]);

        $address = $application->residentialAddresses()->create($validated);
        $address->calculateMonthsAtAddress();

        ActivityLog::logActivity(
            'created',
            "Added {$validated['address_type']} address",
            $address,
            null,
            $validated
        );

        return back()->with('success', 'Address added successfully.');
    }

    public function update(Request $request, Application $application, ResidentialAddress $residentialAddress)
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'address_type' => 'required|in:current,previous_1,previous_2,previous_3',
            'street_address' => 'required|string|max:255',
            'suburb' => 'required|string|max:100',
            'state' => 'required|string|max:50',
            'postcode' => 'required|string|max:10',
            'country' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'residential_status' => 'nullable|in:own,rent,boarding,living_with_parents',
        ]);

        $oldValues = $residentialAddress->toArray();
        $residentialAddress->update($validated);
        $residentialAddress->calculateMonthsAtAddress();

        ActivityLog::logActivity(
            'updated',
            "Updated {$validated['address_type']} address",
            $residentialAddress,
            $oldValues,
            $validated
        );

        return back()->with('success', 'Address updated successfully.');
    }

    public function destroy(Application $application, ResidentialAddress $residentialAddress)
    {
        $this->authorize('update', $application);

        $addressType = $residentialAddress->address_type;
        $residentialAddress->delete();

        ActivityLog::logActivity(
            'deleted',
            "Deleted {$addressType} address",
            $application
        );

        return back()->with('success', 'Address deleted successfully.');
    }
}
