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
            'residential_status' => 'required|in:owner_no_mortgage,owner_with_mortgage,renting,boarding,living_with_parents,other',
            'street_address' => 'required|string|max:255',
            'suburb' => 'required|string|max:100',
            'state' => 'required|in:NSW,VIC,QLD,SA,WA,TAS,NT,ACT',
            'postcode' => 'required|string|size:4',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        // Calculate months at address
        $startDate = new \DateTime($validated['start_date']);
        $endDate = $validated['end_date'] ? new \DateTime($validated['end_date']) : new \DateTime();
        $interval = $startDate->diff($endDate);
        $monthsAtAddress = ($interval->y * 12) + $interval->m;

        $address = $application->residentialAddresses()->create(array_merge(
            $validated,
            ['months_at_address' => $monthsAtAddress]
        ));

        ActivityLog::logActivity(
            'created',
            'Residential address added',
            $application
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
