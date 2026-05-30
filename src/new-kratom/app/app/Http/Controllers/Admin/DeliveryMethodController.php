<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DeliveryMethodController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => DeliveryMethod::orderBy('position')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());
        $method = DeliveryMethod::create($data);
        return response()->json(['data' => $method], 201);
    }

    public function show(DeliveryMethod $deliveryMethod): JsonResponse
    {
        return response()->json(['data' => $deliveryMethod]);
    }

    public function update(Request $request, DeliveryMethod $deliveryMethod): JsonResponse
    {
        $data = $request->validate($this->rules($deliveryMethod->id));
        $deliveryMethod->update($data);
        return response()->json(['data' => $deliveryMethod->fresh()]);
    }

    public function destroy(DeliveryMethod $deliveryMethod): JsonResponse
    {
        $deliveryMethod->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * Массовая смена порядка: { order: [id, id, …] }.
     * Позиции присваиваются плотно от 1, пропуски/дубликаты безопасно игнорируются.
     */
    public function reorder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer', 'min:1'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['order'] as $position => $id) {
                DeliveryMethod::where('id', $id)->update(['position' => $position + 1]);
            }
        });

        return response()->json([
            'data' => DeliveryMethod::orderBy('position')->orderBy('id')->get(),
        ]);
    }

    /** @return array<string, mixed> */
    private function rules(?int $id = null): array
    {
        return [
            'code' => ['required', 'string', 'max:64', Rule::unique('delivery_methods', 'code')->ignore($id)],
            'type' => ['required', Rule::in(['pickup', 'courier'])],
            'name' => ['required', 'array'],
            'name.cs' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'array'],
            'eta' => ['nullable', 'array'],
            'address' => ['nullable', 'array'],
            'address.street' => ['nullable', 'string', 'max:128'],
            'address.city' => ['nullable', 'string', 'max:64'],
            'address.zip' => ['nullable', 'string', 'max:16'],
            'address.hours' => ['nullable', 'string', 'max:120'],
            'price' => ['required', 'integer', 'min:0'],
            'free_above' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
