<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NpSettlement;
use App\Models\NpWarehouse;
use App\Services\NovaPoshta\NovaPoshtaClient;
use App\Services\NovaPoshta\NpSearchService;
use Illuminate\Http\Request;

class NovaposhtaController extends Controller
{
    public function settlements(Request $request, NpSearchService $searchService)
    {
        $query = $this->inputString($request, ['q', 'find']);
        $ref = $this->inputString($request, ['ref']);
        $popular = $request->boolean('popular');
        $limit = (int) ($request->input('limit') ?: 20);

        if ($ref !== '') {
            $items = NpSettlement::query()->where('ref', $ref)->limit(1)->get();
        } elseif ($popular && $query === '') {
            $items = $searchService->popularSettlements($limit);
        } else {
            $items = $searchService->searchSettlements($query, $limit);
        }

        return response()->json([
            'data' => $items->map(fn (NpSettlement $item) => $this->formatSettlement($item)),
        ]);
    }

    public function warehouses(Request $request, NpSearchService $searchService)
    {
        $query = $this->inputString($request, ['q', 'find']);
        $settlementRef = $this->inputString($request, ['settlementRef', 'settlement_ref']);
        $limit = (int) ($request->input('limit') ?: 100);

        if ($settlementRef === '') {
            return response()->json(['data' => []]);
        }

        $items = $searchService->searchWarehouses($query, $settlementRef, $limit);

        return response()->json([
            'data' => $items->map(fn (NpWarehouse $item) => $this->formatWarehouse($item)),
        ]);
    }

    public function streets(Request $request, NovaPoshtaClient $client)
    {
        $query = $this->inputString($request, ['q', 'find']);
        $settlementRef = $this->inputString($request, ['settlementRef', 'settlement_ref']);
        $limit = (int) ($request->input('limit') ?: 50);

        if ($settlementRef === '') {
            return response()->json(['data' => []]);
        }

        $data = $client->searchStreets($settlementRef, $query !== '' ? $query : null, $limit);

        return response()->json(['data' => $data]);
    }

    private function formatSettlement(NpSettlement $settlement): array
    {
        return [
            'Ref' => $settlement->ref,
            'Description' => $settlement->name_uk,
            'DescriptionRu' => $settlement->name_ru,
            'AreaDescription' => $settlement->area_uk,
            'AreaDescriptionRu' => $settlement->area_ru,
            'RegionDescription' => $settlement->region_uk,
            'RegionDescriptionRu' => $settlement->region_ru,
            'RegionsDescription' => $settlement->region_uk,
            'RegionsDescriptionRu' => $settlement->region_ru,
            'SettlementTypeDescription' => $settlement->type_uk,
            'SettlementTypeDescriptionRu' => $settlement->type_ru,
        ];
    }

    private function formatWarehouse(NpWarehouse $warehouse): array
    {
        return [
            'Ref' => $warehouse->ref,
            'Description' => $warehouse->name_uk,
            'DescriptionRu' => $warehouse->name_ru,
            'SettlementRef' => $warehouse->settlement_ref,
            'CategoryOfWarehouse' => $warehouse->category,
            'TypeOfWarehouse' => $warehouse->type,
        ];
    }

    private function inputString(Request $request, array $keys): string
    {
        foreach ($keys as $key) {
            $value = $request->input($key);
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return '';
    }
}
