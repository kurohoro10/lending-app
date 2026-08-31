<?php

namespace App\Http\Controllers;

use App\Helpers\AustralianSuburbs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuburbSearchController extends Controller
{
    /**
     * Local suburb search — no external API call.
     * Backed by App\Helpers\AustralianSuburbs::search().
     *
     * GET /api/suburbs/search?q=syd&limit=8
     * Returns: [{ suburb, state, postcode }]
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q'     => 'required|string|min:2|max:100',
            'limit' => 'sometimes|integer|min:1|max:15',
        ]);

        $query = trim($request->input('q'));
        $limit = $request->integer('limit', 8);

        $results = AustralianSuburbs::search($query, $limit);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }
}