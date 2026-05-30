<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CitySearchController extends Controller
{
    /**
     * Autocomplete endpoint backing the address form. Falls back to a plain
     * SQL LIKE query if Meilisearch is unreachable so the page stays usable.
     */
    public function index(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $limit = (int) $request->query('limit', 12);
        $limit = max(1, min($limit, 25));

        if ($q === '') {
            return response()->json(['data' => []]);
        }

        try {
            $hits = City::search($q)
                ->take($limit)
                ->get();
        } catch (\Throwable $e) {
            $hits = City::query()
                ->where(function ($w) use ($q) {
                    $w->where('name', 'like', $q . '%')
                        ->orWhere('ascii_name', 'like', $q . '%');
                })
                ->orderByDesc('population')
                ->limit($limit)
                ->get();
        }

        return response()->json([
            'data' => $hits->map(fn (City $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'district_name' => $c->district_name,
                'region_name' => $c->region_name,
                'full_label' => $c->full_label,
            ])->values(),
        ]);
    }
}
