<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ResidentialAddress;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ResidentialAddressController extends Controller
{
    /**
     * Minimum required address history in months (3 years).
     */
    private const REQUIRED_MONTHS = 36;

    public function store(Request $request, Application $application): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'address_type'       => 'required|in:current,previous_1,previous_2,previous_3',
            'residential_status' => 'required|in:owner_no_mortgage,owner_with_mortgage,renting,boarding,living_with_parents,other',
            'street_address'     => 'required|string|max:255',
            'suburb'             => 'required|string|max:100',
            'state'              => 'required|in:NSW,VIC,QLD,SA,WA,TAS,NT,ACT',
            'postcode'           => 'required|string|size:4',
            'start_date'         => 'required|date',
            'end_date'           => 'nullable|date|after:start_date',
        ]);

        $startDate = new \DateTime($validated['start_date']);
        $endDate   = $validated['end_date'] ? new \DateTime($validated['end_date']) : new \DateTime();
        $interval  = $startDate->diff($endDate);

        $address = $application->residentialAddresses()->create(array_merge(
            $validated,
            ['months_at_address' => ($interval->y * 12) + $interval->m]
        ));

        ActivityLog::logActivity('created', 'Residential address added', $application);

        $coverage = $this->calculateCoverage($application->fresh()->residentialAddresses);

        if ($request->expectsJson()) {
            return response()->json([
                'success'                => true,
                'message'                => 'Address added successfully.',
                'address'                => $address,
                'type'                   => 'address',
                'trigger_progress_update' => true,
                'coverage'               => $coverage,
            ], 201);
        }

        return back()->with('success', 'Address added successfully.');
    }

    public function update(Request $request, Application $application, ResidentialAddress $residentialAddress): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'address_type'       => 'required|in:current,previous_1,previous_2,previous_3',
            'street_address'     => 'required|string|max:255',
            'suburb'             => 'required|string|max:100',
            'state'              => 'required|string|max:50',
            'postcode'           => 'required|string|max:10',
            'country'            => 'required|string|max:100',
            'start_date'         => 'required|date',
            'end_date'           => 'nullable|date|after:start_date',
            'residential_status' => 'nullable|in:owner_no_mortgage,owner_with_mortgage,renting,boarding,living_with_parents,other',
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

        $coverage = $this->calculateCoverage($application->fresh()->residentialAddresses);

        if ($request->expectsJson()) {
            return response()->json([
                'success'                => true,
                'message'                => 'Address updated successfully.',
                'address'                => $residentialAddress,
                'type'                   => 'address',
                'trigger_progress_update' => true,
                'coverage'               => $coverage,
            ]);
        }

        return back()->with('success', 'Address updated successfully.');
    }

    public function destroy(Request $request, Application $application, ResidentialAddress $residentialAddress): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $application);

        $addressType = $residentialAddress->address_type;
        $addressId   = $residentialAddress->id;

        $residentialAddress->delete();

        ActivityLog::logActivity('deleted', "Deleted {$addressType} address", $application);

        $coverage = $this->calculateCoverage($application->fresh()->residentialAddresses);

        if ($request->expectsJson()) {
            return response()->json([
                'success'                => true,
                'message'                => 'Address deleted successfully.',
                'deleted_id'             => $addressId,
                'type'                   => 'address',
                'trigger_progress_update' => true,
                'coverage'               => $coverage,
            ]);
        }

        return back()->with('success', 'Address deleted successfully.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Calculate total address history coverage across all addresses.
     * Merges overlapping date ranges so overlaps don't count twice.
     */
    private function calculateCoverage($addresses): array
    {
        $totalMonths = $this->sumMonthsWithoutOverlap($addresses);
        $met         = $totalMonths >= self::REQUIRED_MONTHS;

        return [
            'total_months'    => $totalMonths,
            'required_months' => self::REQUIRED_MONTHS,
            'met'             => $met,
            'percentage'      => min(100, round(($totalMonths / self::REQUIRED_MONTHS) * 100)),
            'message'         => $met
                ? 'Address history requirement met.'
                : sprintf(
                    'You need %d more month(s) of address history (%d of %d months covered).',
                    self::REQUIRED_MONTHS - $totalMonths,
                    $totalMonths,
                    self::REQUIRED_MONTHS
                ),
        ];
    }

    /**
     * Sum months across all address records, merging overlapping date ranges
     * so that overlapping periods don't get double-counted.
     */
    private function sumMonthsWithoutOverlap($addresses): int
    {
        if ($addresses->isEmpty()) {
            return 0;
        }

        // Build [start, end] timestamp pairs
        $ranges = $addresses->map(function ($address) {
            $start = $address->start_date instanceof \Carbon\Carbon
                ? $address->start_date
                : \Carbon\Carbon::parse($address->start_date);

            $end = $address->end_date
                ? ($address->end_date instanceof \Carbon\Carbon
                    ? $address->end_date
                    : \Carbon\Carbon::parse($address->end_date))
                : now();

            return [$start->timestamp, $end->timestamp];
        })->sortBy(fn($r) => $r[0])->values()->toArray();

        // Merge overlapping ranges
        $merged = [];
        foreach ($ranges as [$start, $end]) {
            if (empty($merged)) {
                $merged[] = [$start, $end];
                continue;
            }

            $last = &$merged[count($merged) - 1];
            if ($start <= $last[1]) {
                // Overlapping — extend if needed
                $last[1] = max($last[1], $end);
            } else {
                $merged[] = [$start, $end];
            }
        }

        // Sum merged ranges in months
        $totalMonths = 0;
        foreach ($merged as [$start, $end]) {
            $totalMonths += (int) round(($end - $start) / (60 * 60 * 24 * 30.44));
        }

        return $totalMonths;
    }
}
