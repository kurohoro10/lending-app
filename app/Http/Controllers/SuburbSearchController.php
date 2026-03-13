<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SuburbSearchController extends Controller
{
    /**
     * Free, no-key Australian suburb search via postcodeapi.com.au
     *
     * Docs: https://v0.postcodeapi.com.au/
     * Rate limit: 100 unique requests / hour (fair-use, no auth required)
     *
     * GET /api/suburbs/search?q=syd&limit=8
     * Returns: [{ suburb, state, postcode }]
     */
    private const API_URL = 'https://v0.postcodeapi.com.au/suburbs.json';

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q'     => 'required|string|min:2|max:100',
            'limit' => 'sometimes|integer|min:1|max:15',
        ]);

        $query = trim($request->input('q'));
        $limit = $request->integer('limit', 8);

        try {
            $response = Http::timeout(5)
                ->withHeaders(['Accept' => 'application/json'])
                ->get(self::API_URL, ['q' => $query]);

            if (!$response->successful()) {
                Log::warning('postcodeapi.com.au request failed', [
                    'status' => $response->status(),
                    'query'  => $query,
                ]);

                return response()->json([
                    'success' => false,
                    'results' => [],
                    'message' => 'Suburb search unavailable. Please try again.',
                ], 502);
            }

            $raw     = $response->json() ?? [];
            $results = collect($raw)
                ->map(fn($item) => $this->normalise($item))
                ->filter(fn($item) => $item !== null)                 // drop malformed entries
                ->unique(fn($item) => $item['suburb'] . $item['state']) // dedupe same-name suburbs across postcodes
                ->take($limit)
                ->values()
                ->all();

            return response()->json([
                'success' => true,
                'results' => $results,
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('postcodeapi.com.au connection error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'results' => [],
                'message' => 'Could not reach suburb search service. Please try again.',
            ], 503);
        }
    }

    /**
     * Normalise a single postcodeapi.com.au result into our { suburb, state, postcode } shape.
     * Returns null if the entry is missing required fields.
     *
     * postcodeapi.com.au response shape:
     * {
     *   "name":     "Sydney",
     *   "postcode": 2000,
     *   "state": {
     *     "name":         "New South Wales",
     *     "abbreviation": "NSW"
     *   },
     *   ...
     * }
     */
    private function normalise(array $item): ?array
    {
        $suburb   = $item['name'] ?? null;
        $state    = $item['state']['abbreviation'] ?? null;
        $postcode = isset($item['postcode']) ? str_pad((string) $item['postcode'], 4, '0', STR_PAD_LEFT) : null;

        if (!$suburb || !$state || !$postcode) {
            return null;
        }

        // Ensure state is a recognised AU code (guards against unexpected data)
        $validStates = ['NSW', 'VIC', 'QLD', 'SA', 'WA', 'TAS', 'NT', 'ACT'];
        if (!in_array(strtoupper($state), $validStates, true)) {
            return null;
        }

        return [
            'suburb'   => ucwords(strtolower($suburb)),   // "BONDI BEACH" → "Bondi Beach"
            'state'    => strtoupper($state),
            'postcode' => $postcode,
        ];
    }
}